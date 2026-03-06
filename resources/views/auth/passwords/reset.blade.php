@extends('layouts.app')

@section('content')

<!-- Reset Password -->
<section class="m-top mb-60">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 m-auto">
                <div class="widget">
                    <h5 class="widget__title">Reset Password</h5>

                    <p class="text-muted mb-3">
                        Enter your new password below to reset your account password.
                    </p>

                    <form action="{{ route('create.new-password') }}" method="POST" class="widget__form">
                        @csrf

                        {{-- Hidden Email --}}
                        <input type="hidden" name="user_email" value="{{ $email }}">
                        @error('user_email')
                            <small class="text-danger d-block mb-2">{{ $message }}</small>
                        @enderror

                        {{-- Password --}}
                        <div class="form-group">
                            <input
                                type="password"
                                name="password"
                                class="form-control widget__form-input @error('password') is-invalid @enderror"
                                placeholder="Enter new password*"
                                required
                            >
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div class="form-group">
                            <input
                                type="password"
                                name="confirm_password"
                                class="form-control widget__form-input @error('confirm_password') is-invalid @enderror"
                                placeholder="Confirm new password*"
                                required
                            >
                            @error('confirm_password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="widget__form-btn mt-3">
                            <button type="submit" class="btn-custom w-100">
                                Reset Password
                            </button>
                        </div>
                    </form>

                    {{-- Optional link back to login --}}
                    <div class="mt-3 text-center">
                        <a href="{{ route('login') }}" class="widget__form-link">
                            Back to Login
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

@endsection