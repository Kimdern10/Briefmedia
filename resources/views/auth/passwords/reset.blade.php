@extends('layouts.app')

@section('title', 'Reset password | ' . ($globalSeo->site_name ?? config('app.name')))
@section('auth_page', true)

@section('content')
<main class="bm-auth">
    <div class="bm-auth__card">
        <h1 class="bm-auth__title">Set a new password</h1>
        <p class="bm-auth__sub">Choose a strong password you haven't used before.</p>

        @include('snippets.site.auth-alerts')

        <form action="{{ route('create.new-password') }}" method="POST">
            @csrf
            <input type="hidden" name="user_email" value="{{ $email }}">
            @error('user_email')
                <div class="bm-alert bm-alert--error">{{ $message }}</div>
            @enderror

            <div class="bm-field">
                <label class="bm-label" for="password">New password</label>
                <input id="password" type="password" name="password" autocomplete="new-password" required
                       class="bm-input @error('password') is-invalid @enderror" placeholder="Enter new password">
                @error('password')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="bm-field">
                <label class="bm-label" for="confirm_password">Confirm new password</label>
                <input id="confirm_password" type="password" name="confirm_password" autocomplete="new-password" required
                       class="bm-input @error('confirm_password') is-invalid @enderror" placeholder="Repeat new password">
                @error('confirm_password')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="bm-btn bm-btn--primary bm-btn--block">Reset password</button>
        </form>

        <p class="bm-auth__alt"><a href="{{ route('login') }}">Back to log in</a></p>
    </div>
</main>
@endsection
