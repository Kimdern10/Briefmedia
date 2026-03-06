<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    //
    public function signUp()
    {
        return view ('auth.register');
    }

    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email|max:255|string',
            'password' => 'required|min:5|max:40',
            'confirm_password' => 'required|min:5|max:40|same:password',
             'terms' => 'accepted',
        ], [
            "email.unique" => "This email is already registered, please sign in or use a different email",
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->terms_accepted = $request->has('terms');

        // Generate OTP and expiration
        $user->email_verification_otp = rand(100000, 999999);
        $user->otp_expires_at = Carbon::now()->addMinutes(5);

        if ($user->save()) {
            try {
                Mail::to($user->email)->send(new OtpMail($user->email_verification_otp));
            } catch (\Exception $e) {
                // Log the error
                Log::error("Failed to send OTP email: " . $e->getMessage());

                // Redirect user with a friendly message
                return redirect()->route('verify.otp', ['email' => $user->email])
                                 ->with('warning', 'Registered successfully but OTP email could not be sent. Please check your network connection and request a new OTP.');
            }

            return redirect()->route('verify.otp', ['email' => $user->email]);
        } else {
            return redirect()->back()->with('error', 'Registration Failed');
        }
    }

    public function user_dashboard()
    {
        return view('welcome');
    }

    public function showOtpForm($email)
    {
        $user = User::where('email', $email)->first();

        if (!$user || !$user->otp_expires_at) {
            return redirect()->route('login')->with('error', 'Invalid request. Please register again.');
        }

        $otpExpiresAt = Carbon::parse($user->otp_expires_at)->timestamp;

        return view('auth.verify-ot', compact('email', 'otpExpiresAt'));
    }

    public function submitOtp(Request $request, $email)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6'
        ]);

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        if ($user->otp_expires_at === null || Carbon::now()->greaterThan($user->otp_expires_at)) {
            return redirect()->back()->with('error', 'OTP has expired , please request a new one.');
        }

        if ($user->email_verification_otp != $request->otp) {
            return redirect()->back()->with('error', 'The code you entered is invalid');
        }

        $user->email_verified_at = Carbon::now();
        $user->email_verification_otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return redirect()->route('login')->with('message', 'Email verified successfully, please login');
    }

    public function resendOtp($email)
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->email_verification_otp = rand(100000, 999999);
            $user->otp_expires_at = Carbon::now()->addMinutes(5);
            $user->save();

            try {
                Mail::to($user->email)->send(new OtpMail($user->email_verification_otp));
            } catch (\Exception $e) {
                Log::error("Failed to resend OTP email: " . $e->getMessage());
                return redirect()->back()->with('warning', 'Could not send OTP email. Please check your network connection and try again later.');
            }

            return redirect()->back()->with('message', 'A new OTP has been sent to your email');
        }

        return redirect()->route('login')->with('error', 'User not found');
    }

    /**
     * Search posts by keyword (simple version)
     */
    public function searchPosts(Request $request)
    {
        // Get the search query
        $query = $request->input('query');
        
        // If no query, redirect back or show all posts
        if (empty($query)) {
            return redirect()->back()->with('error', 'Please enter a search term');
        }
        
        // Search in posts
        $posts = Post::where('title', 'LIKE', "%{$query}%")
                      ->orWhere('content', 'LIKE', "%{$query}%")
                      ->orWhere('excerpt', 'LIKE', "%{$query}%")
                      ->latest()
                      ->paginate(10);
        
        // Get categories for sidebar if needed
        $categories = Category::all();
        
        // Return view with results
        return view('user.posts.search', compact('posts', 'query', 'categories'));
    }

    public function blade()
    {
        // No need to query anything here since all variables come from AppServiceProvider
        return view('welcome');
    }
}