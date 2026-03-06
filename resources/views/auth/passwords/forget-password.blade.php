@extends('layouts.app')

@section('content')

<!-- Forgot Password -->
<section class="m-top mb-60">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 m-auto">
                <div class="widget">
                    <h5 class="widget__title">Forgot Password</h5>

                    <p class="widget__form-text mb-3">
                        No worries, we’ll send a verification code to your email to reset your password.
                    </p>

                    <!-- Success message -->
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form action="{{ route('forgotPassword.email') }}" class="widget__form" method="POST">
                        @csrf

                        <!-- Email -->
                        <div class="form-group">
                            <input
                                type="email"
                                class="form-control widget__form-input"
                                placeholder="Email Address*"
                                name="email"
                                value="{{ old('email') }}"
                                required
                            >
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <div class="widget__form-btn">
                            <button type="submit" class="btn-custom">
                                Send Reset Code
                            </button>
                        </div>

                        <p class="widget__form-text mt-3">
                            Remember your password?
                            <a href="{{ route('login') }}" class="widget__form-link">
                                Login
                            </a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->


@endsection
