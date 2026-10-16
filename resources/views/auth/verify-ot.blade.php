@extends('layouts.app')

@section('title', 'Verify your email | ' . ($globalSeo->site_name ?? config('app.name')))
@section('auth_page', true)

@section('content')
<main class="bm-auth">
    <div class="bm-auth__card">
        <h1 class="bm-auth__title">Check your inbox</h1>
        <p class="bm-auth__sub">Enter the 6-digit code we sent to <strong>{{ $email }}</strong>.</p>

        @include('snippets.site.auth-alerts')

        <form action="{{ route('verify.otp.submit', ['email' => $email]) }}" method="POST">
            @csrf

            <div class="bm-field">
                <label class="bm-label" for="otp">Verification code</label>
                <input id="otp" type="text" name="otp" value="{{ old('otp') }}" maxlength="6" inputmode="numeric"
                       autocomplete="one-time-code" required autofocus
                       class="bm-input bm-input--otp @error('otp') is-invalid @enderror" placeholder="000000">
                @error('otp')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="bm-auth__row">
                <span id="countdown-timer" class="bm-meta"></span>
                <a href="{{ route('resend.otp', ['email' => $email]) }}" id="resend-btn"
                   style="pointer-events: none; opacity: .5;"
                   onclick="localStorage.removeItem('otp-expiry-{{ $email }}')">Resend code</a>
            </div>

            <button type="submit" class="bm-btn bm-btn--primary bm-btn--block">Verify email</button>
        </form>
    </div>
</main>

@include('snippets.site.otp-countdown', ['email' => $email, 'otpExpiresAt' => $otpExpiresAt ?? null, 'expiredText' => 'Code expired. You can resend it now.'])
@endsection
