@extends('emails.layout')

@section('title', 'Verify your email')
@section('preheader', 'Your BriefMedia verification code is ' . $otp_code . '. It expires in ' . ($expiresIn ?? '5 minutes') . '.')

@section('content')
    <h1 class="bm-h1" style="margin:0 0 12px; font-family:Georgia, 'Times New Roman', serif; font-size:28px; line-height:1.2; font-weight:bold; color:#121216;">
        Confirm it's you
    </h1>
    <p style="margin:0;">
        Welcome to <strong style="color:#121216;">BriefMedia</strong>, your source for news, entertainment and stories that matter.
        Enter this code on the verification page to finish setting up your account.
    </p>

    @include('emails.partials.code', ['code' => $otp_code, 'expiresIn' => $expiresIn ?? '5 minutes'])

    <p style="margin:0 0 6px; font-size:13.5px; color:#6e6e7a;">
        For your security, never share this code with anyone. BriefMedia staff will never ask for it.
    </p>
    <p style="margin:0; font-size:13.5px; color:#6e6e7a;">
        Didn't try to sign in or create an account? You can safely ignore this email.
    </p>
@endsection
