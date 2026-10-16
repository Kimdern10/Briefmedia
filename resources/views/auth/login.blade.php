@extends('layouts.app')

@section('title', 'Log in | ' . ($globalSeo->site_name ?? config('app.name')))
@section('auth_page', true)

@section('content')
<main class="bm-auth">
    <div class="bm-auth__card">
        <h1 class="bm-auth__title">Welcome back</h1>
        <p class="bm-auth__sub">Log in to like, comment and join the conversation.</p>

        @include('snippets.site.auth-alerts')

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="bm-field">
                <label class="bm-label" for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                       class="bm-input @error('email') is-invalid @enderror" placeholder="you@example.com">
                @error('email')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="bm-field">
                <label class="bm-label" for="password">Password</label>
                <input id="password" type="password" name="password" autocomplete="current-password" required
                       class="bm-input @error('password') is-invalid @enderror" placeholder="••••••••">
                @error('password')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="bm-auth__row">
                <label class="bm-check" for="rememberMe">
                    <input type="checkbox" id="rememberMe" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Remember me
                </label>
                <a href="{{ route('forgetPassword') }}">Forgot password?</a>
            </div>

            <button type="submit" class="bm-btn bm-btn--primary bm-btn--block">Log in</button>
        </form>

        <p class="bm-auth__alt">New to BriefMedia? <a href="{{ route('sign-up') }}">Create an account</a></p>
    </div>
</main>
@endsection
