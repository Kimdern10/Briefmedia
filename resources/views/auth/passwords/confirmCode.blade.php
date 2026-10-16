@extends('layouts.app')

@section('title', 'Confirm code | ' . ($globalSeo->site_name ?? config('app.name')))
@section('auth_page', true)

@section('content')
<main class="bm-auth">
    <div class="bm-auth__card">
        <h1 class="bm-auth__title">Enter your code</h1>
        <p class="bm-auth__sub">We sent a verification code to <strong>{{ $email }}</strong>.</p>

        @include('snippets.site.auth-alerts')

        <form action="{{ route('submitPasswordResetCode') }}" method="POST">
            @csrf
            <input type="hidden" name="user_email" value="{{ $email }}">
            @error('user_email')
                <div class="bm-alert bm-alert--error">{{ $message }}</div>
            @enderror

            <div class="bm-field">
                <label class="bm-label" for="code">Verification code</label>
                <input id="code" type="text" name="code" value="{{ old('code') }}" inputmode="numeric"
                       autocomplete="one-time-code" required autofocus
                       class="bm-input bm-input--otp @error('code') is-invalid @enderror" placeholder="000000">
                @error('code')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="bm-auth__row">
                <span id="countdown-timer" class="bm-meta"></span>
                <a href="{{ route('resend.code', ['email' => $email]) }}" id="resend-btn"
                   style="pointer-events: none; opacity: .5;"
                   onclick="localStorage.removeItem('otp-expiry-{{ $email }}')">Resend code</a>
            </div>

            <button type="submit" class="bm-btn bm-btn--primary bm-btn--block">Verify code</button>
        </form>

        <p class="bm-auth__alt"><a href="{{ route('login') }}">Back to log in</a></p>
    </div>
</main>

@include('snippets.site.otp-countdown', ['email' => $email, 'otpExpiresAt' => $otpExpiresAt ?? null, 'expiredText' => 'Code expired. You can resend it now.'])
@endsection
