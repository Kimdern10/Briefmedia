{{-- Sent both as PasswordResetMail ($code) and via Mail::send in ForgotPasswordController ($body). --}}
@php $resetCode = $code ?? $body ?? '------'; @endphp

@extends('emails.layout')

@section('title', 'Reset your password')
@section('preheader', 'Your BriefMedia password reset code is ' . $resetCode . '.')

@section('content')
    <h1 class="bm-h1" style="margin:0 0 12px; font-family:Georgia, 'Times New Roman', serif; font-size:28px; line-height:1.2; font-weight:bold; color:#121216;">
        Reset your password
    </h1>
    <p style="margin:0;">
        Hi{{ isset($name) ? ' ' . e($name) : '' }}, we received a request to reset the password for your BriefMedia account.
        Enter this code on the reset page to choose a new password.
    </p>

    @include('emails.partials.code', ['code' => $resetCode, 'expiresIn' => $expiresIn ?? '5 minutes'])

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 18px;">
        <tr>
            <td style="background-color:#f7f5f2; border-radius:12px; padding:14px 16px; font-size:13.5px; color:#3a3a44;">
                <strong style="color:#121216;">Didn't ask for this?</strong>
                Ignore this email and your password stays the same. If you keep getting these, contact
                <a href="mailto:{{ $supportEmail ?? 'support@briefMediablog.com' }}" style="color:#e4407f; font-weight:bold; text-decoration:none;">{{ $supportEmail ?? 'support@briefMediablog.com' }}</a>.
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:13.5px; color:#6e6e7a;">Never share this code with anyone, including BriefMedia staff.</p>
@endsection
