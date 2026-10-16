@extends('layouts.app')

@section('title', 'Sign up | ' . ($globalSeo->site_name ?? config('app.name')))
@section('auth_page', true)

@section('content')
<main class="bm-auth">
    <div class="bm-auth__card">
        <h1 class="bm-auth__title">Create your account</h1>
        <p class="bm-auth__sub">Join the conversation. It only takes a minute.</p>

        @include('snippets.site.auth-alerts')

        <form method="POST" action="{{ route('create-user') }}">
            @csrf

            <div class="bm-field">
                <label class="bm-label" for="name">Full name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required
                       class="bm-input @error('name') is-invalid @enderror" placeholder="Jane Doe">
                @error('name')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="bm-field">
                <label class="bm-label" for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required
                       class="bm-input @error('email') is-invalid @enderror" placeholder="you@example.com">
                @error('email')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="bm-field">
                <label class="bm-label" for="password">Password</label>
                <input id="password" type="password" name="password" autocomplete="new-password" required
                       class="bm-input @error('password') is-invalid @enderror" placeholder="At least 5 characters">
                @error('password')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="bm-field">
                <label class="bm-label" for="confirm_password">Confirm password</label>
                <input id="confirm_password" type="password" name="confirm_password" autocomplete="new-password" required
                       class="bm-input @error('confirm_password') is-invalid @enderror" placeholder="Repeat your password">
                @error('confirm_password')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="bm-field">
                <label class="bm-check" for="terms">
                    <input type="checkbox" id="terms" name="terms" required {{ old('terms') ? 'checked' : '' }}>
                    <span>I agree to the <a href="{{ route('terms') }}" style="color: var(--bm-accent); font-weight: 600;">Terms &amp; Conditions</a></span>
                </label>
                @error('terms')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="bm-btn bm-btn--primary bm-btn--block">Create account</button>
        </form>

        <p class="bm-auth__alt">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
    </div>
</main>
@endsection
