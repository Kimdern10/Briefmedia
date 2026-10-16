@extends('layouts.app')

@section('title', 'Forgot password | ' . ($globalSeo->site_name ?? config('app.name')))
@section('auth_page', true)

@section('content')
<main class="bm-auth">
    <div class="bm-auth__card">
        <h1 class="bm-auth__title">Forgot password?</h1>
        <p class="bm-auth__sub">No worries, we'll email you a verification code to reset it.</p>

        @include('snippets.site.auth-alerts')

        <form action="{{ route('forgotPassword.email') }}" method="POST">
            @csrf

            <div class="bm-field">
                <label class="bm-label" for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                       class="bm-input @error('email') is-invalid @enderror" placeholder="you@example.com">
                @error('email')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="bm-btn bm-btn--primary bm-btn--block">Send reset code</button>
        </form>

        <p class="bm-auth__alt">Remember your password? <a href="{{ route('login') }}">Log in</a></p>
    </div>
</main>
@endsection
