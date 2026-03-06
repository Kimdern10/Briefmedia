<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\otpMail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/home';


    public function authenticated()
    {
        $user = Auth::user();

        // 🔒 Check if user is active
        if ($user->active == 0) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Your account has been banned. Please contact our support.');
        }

        // 🔑 Check admin roles
        if (in_array($user->role_as, ['1', '2', '3'])) {
            return redirect('admin/dashboard')->with('message', 'Welcome to admin dashboard');
        } 
        // 👤 Regular user
        elseif ($user->role_as == "0") {
            // Check if email is verified
            if (!$user->email_verified_at) {

                // ✅ Only generate and send OTP if it's not already stored
                if (!$user->email_verification_otp) {
                    $email_verification_otp = rand(100000, 999999);

                    $user->update([
                        'email_verification_otp' => $email_verification_otp,
                    ]);

                    // Send OTP mail with try/catch
                    try {
                        Mail::to($user->email)->send(new otpMail($email_verification_otp));
                    } catch (\Exception $e) {
                        Log::error("Failed to send OTP email on login: " . $e->getMessage());
                        // Still redirect user, but warn them
                        Auth::logout();
                        return redirect()->route('verify.otp', ['email' => $user->email])
                            ->with('warning', 'Could not send OTP email.  Please check your network connection and try again.');
                    }
                }

                Auth::logout();
                return redirect()->route('verify.otp', ['email' => $user->email])
                    ->with('error', 'Email not verified, please enter the OTP sent to your email.');
            }

            // ✅ Verified users can login
            return redirect()->intended(route('user.dashboard'))->with('success', 'Logged in successfully');
        }
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
