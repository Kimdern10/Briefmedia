@extends('layouts.app')

@section('content')
   <!-- Login -->
<section class="m-top mb-60">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 m-auto">
                <div class="widget">
                    <h5 class="widget__title">Login</h5>

                    <form action="{{ route('login') }}" class="widget__form" method="POST">
                        @csrf

                        <!-- Email -->
                        <div class="form-group">
                            <input
                                type="email"
                                class="form-control widget__form-input"
                                placeholder="Email Address*"
                                name="email"
                                value="{{ old('email') }}"
                            >
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <input
                                type="password"
                                class="form-control widget__form-input"
                                placeholder="Password*"
                                name="password"
                            >
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                       <div class="widget__form-controls form-group d-flex align-items-center">
    <div class="widget__form-controls-checkbox">
        <input
            type="checkbox"
            class="widget__form-controls-input"
            id="rememberMe"
            name="remember"
            {{ old('remember') ? 'checked' : '' }}
        >
        <label class="widget__form-controls-label" for="rememberMe">
            Remember Me
        </label>
    </div>

    <a href="{{ route('forgetPassword') }}" class="widget__form-link ml-auto">
        Forgot Password?
    </a>
</div>
                        <!-- Submit -->
                        <div class="widget__form-btn">
                            <button type="submit" class="btn-custom">Login Now</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

 <!--newslettre-->
        

@endsection
