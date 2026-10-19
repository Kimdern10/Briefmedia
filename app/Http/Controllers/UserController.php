<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CacheGuestPages;
use App\Mail\OtpMail;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        // Guests get the saved homepage HTML (set here, not on the route, so no route change is needed)
        $this->middleware(CacheGuestPages::class)->only('blade');
    }

    public function signUp()
    {
        return view('auth.register');
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
            'email.unique' => 'This email is already registered, please sign in or use a different email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = new User;
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
                Log::error('Failed to send OTP email: '.$e->getMessage());

                // Redirect user with a friendly message
                return redirect()->route('verify.otp', ['email' => $user->email])
                    ->with('warning', 'Registered successfully but OTP email could not be sent. Please check your network connection and request a new OTP.');
            }

            return redirect()->route('verify.otp', ['email' => $user->email]);
        } else {
            return redirect()->back()->with('error', 'Registration Failed');
        }
    }

    /**
     * Old /user/dashboard address (saved in browsers' history and suggestions): sends everyone to the news homepage.
     */
    public function user_dashboard(Request $request): RedirectResponse
    {
        // Keep a message like "Logged in successfully" for the homepage
        $request->session()->reflash();

        return redirect()->route('home');
    }

    public function showOtpForm($email)
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! $user->otp_expires_at) {
            return redirect()->route('login')->with('error', 'Invalid request. Please register again.');
        }

        $otpExpiresAt = Carbon::parse($user->otp_expires_at)->timestamp;

        return view('auth.verify-ot', compact('email', 'otpExpiresAt'));
    }

    public function submitOtp(Request $request, $email)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);

        $user = User::where('email', $email)->first();

        if (! $user) {
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

        // Readers go straight to the news, signed in; anyone else (banned, staff) signs in as usual
        if ((int) $user->active !== 0 && (string) $user->role_as === '0') {
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('home')->with('success', 'Email verified, welcome to BriefMedia!');
        }

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
                Log::error('Failed to resend OTP email: '.$e->getMessage());

                return redirect()->back()->with('warning', 'Could not send OTP email. Please check your network connection and try again later.');
            }

            return redirect()->back()->with('message', 'A new OTP has been sent to your email');
        }

        return redirect()->route('login')->with('error', 'User not found');
    }

    /**
     * Search published stories by keyword and/or date: a year, a month (of any year or of the
     * chosen year) or an exact day. Any of them can be used on its own.
     */
    public function searchPosts(Request $request)
    {
        $query = trim((string) $request->input('query'));

        $year = (int) $request->input('year');
        $year = $year >= 2000 && $year <= (int) now()->year + 1 ? $year : null;

        $month = (int) $request->input('month');
        $month = $month >= 1 && $month <= 12 ? $month : null;

        $day = null;
        if ($request->filled('date')) {
            try {
                $day = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', (string) $request->input('date'))->startOfDay();
            } catch (\Throwable) {
                $day = null;
            }
        }

        $hasFilter = $query !== '' || $year || $month || $day;

        // Search published posts only (drafts must not leak into results)
        $posts = $hasFilter
            ? Post::with(['category', 'admin', 'likedByUsers'])
                ->withCount(['comments', 'likedByUsers'])
                ->published()
                ->when($query !== '', fn ($posts) => $posts->search($query))
                ->when($day, fn ($posts) => $posts->whereDate('created_at', $day->toDateString()))
                ->when(! $day && $year, fn ($posts) => $posts->whereYear('created_at', $year))
                ->when(! $day && $month, fn ($posts) => $posts->whereMonth('created_at', $month))
                ->latest()
                ->paginate(12)
            : new LengthAwarePaginator([], 0, 12);

        return view('user.posts.search', [
            'posts' => $posts,
            'query' => $query,
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'hasFilter' => $hasFilter,
            'years' => Post::publishedYears(),
            'categories' => Category::all(),
        ]);
    }

    public function blade(Request $request)
    {
        // The homepage's lower category blocks, fetched by bm-site.js as the reader scrolls (/?part=more).
        // Served from the homepage's own address so no extra route has to exist on the server.
        if ($request->query('part') === 'more') {
            return response()
                ->view('snippets.site.home-more-sections')
                ->header('X-Robots-Tag', 'noindex');
        }

        // No need to query anything here since all variables come from AppServiceProvider
        return view('welcome');
    }

    /**
     * How many category blocks the homepage sends with the page; /?part=more serves the rest.
     */
    public const HOME_SECTIONS_WITH_PAGE = 2;
}
