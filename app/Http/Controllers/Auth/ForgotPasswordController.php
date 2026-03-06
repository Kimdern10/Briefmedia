<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\otpMail;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    // first step
    protected function forgetPassword()
    {
        return view('auth.passwords.forget-password');
    }

    // second step
    protected function sendEmail($email, $code)
    {
        $data = ['body' => $code];
        $view = 'emails.password_reset';

        try {
            Mail::send($view, $data, function ($message) use ($email) {
                $message->to($email, ' Blog')->subject('Blog  Reset Password');
                // $message->from('omnisanahospital@gmail.com', 'Omnisana Hospital support');
            });
        } catch (\Exception $e) {
            Log::error("Failed to send password reset email: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to send reset Otp email. Please check your network connection try again later.' . $e->getMessage());
        }
    }

    // third step 
    protected function forgotPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            if ($user->activation_code && $user->activation_code_expires_at && now()->lessThan($user->activation_code_expires_at)) {
                $activation_code = $user->activation_code;
            } else {
                $activation_code = random_int(100000, 999999);
                $user->activation_code = $activation_code;
                $user->activation_code_expires_at = now()->addMinutes(5);

                try {
                    $user->save();
                } catch (\Exception $e) {
                    Log::error("Failed to save activation code: " . $e->getMessage());
                    return redirect()->back()->with('error', 'Could not generate reset code. Please try again.');
                }
            }

            $this->sendEmail($user->email, $activation_code);

            return redirect()->route('confirmCode.email', ["user_email" => $user->email]);
        }

        return redirect()->back()->with('error', 'User not found.');
    }

    // fourth step 
    protected function confirmCode()
    {
        $email = request()->user_email;

        $expiresAt = User::where('email', $email)->value('activation_code_expires_at');
        $otpExpiresAt = $expiresAt ? Carbon::parse($expiresAt)->timestamp : null;

        return view('auth.passwords.confirmCode', [
            'email' => $email,
            'otpExpiresAt' => $otpExpiresAt,
        ]);
    }

    // fifth step
    protected function submitPasswordResetCode(Request $request)
    {
        $code = $request->code;
        $email = $request->user_email;

        $account = User::where('email', $email)->first();

        if ($account) {
            if ($code == $account->activation_code) {
                if ($account->activation_code_expires_at && now()->lessThan($account->activation_code_expires_at)) {
                    $account->activation_code = null;
                    $account->activation_code_expires_at = null;

                    try {
                        $account->save();
                    } catch (\Exception $e) {
                        Log::error("Failed to clear activation code: " . $e->getMessage());
                        return redirect()->back()->with('error', 'Could not update account. Please try again.');
                    }

                    return redirect()->route('password-reset', ['user_email' => $email]);
                } else {
                    return redirect()->route('confirmCode.email', ['user_email' => $email])
                        ->with('error', 'Your code has expired. Please request a new one.');
                }
            } else {
                return redirect()->route('confirmCode.email', ['user_email' => $email])
                    ->with('error', 'The code you entered is invalid.');
            }
        }

        return redirect()->route('forgot-password')->with('error', 'User not found.');
    }

    // sixth step 
    protected function passwordReset()
    {
        return view('auth.passwords.reset', ['email' => request()->user_email]);
    }

    // seventh step
    protected function createNewPassword(Request $request)
    {
        if ($request->password !== $request->confirm_password) {
            return redirect()->back()->with('error', 'Password mismatch');
        } else {
            $password = Hash::make($request->password);
            $user = User::where('email', $request->user_email)->first();

            try {
                $user->password = $password;
                $user->save();
            } catch (\Exception $e) {
                Log::error("Failed to update password: " . $e->getMessage());
                return redirect()->back()->with('error', 'Could not update password. Please try again.');
            }

            return redirect()->route('login')->with('message', 'Password updated successfully, please login');
        }
    }

    // eighth step
    public function resendCode(Request $request, $email)
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->activation_code = rand(100000, 999999);
            $user->activation_code_expires_at = now()->addMinutes(5);

            try {
                $user->save();
            } catch (\Exception $e) {
                Log::error("Failed to save new activation code: " . $e->getMessage());
                return redirect()->back()->with('error', 'Could not generate reset code. Please check your network connection try again.');
            }

            try {
                Mail::to($user->email)->send(new PasswordResetMail($user->activation_code));
            } catch (\Exception $e) {
                Log::error("Failed to send password reset code: " . $e->getMessage());
                return redirect()->back()->with('error', 'Could not send reset email. Please try again later.');
            }

            return redirect()->back()->with('message', 'A new Password reset code has been sent to your email');
        }

        return redirect()->route('login')->with('error', 'User not found');
    }
}
