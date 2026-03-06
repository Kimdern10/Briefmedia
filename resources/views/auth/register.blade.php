@extends('layouts.app')

@section('content')

<section class="m-top mb-60">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6 m-auto">
                <div class="widget">
                    <h5 class="widget__title">Sign up</h5>

                    <form class="widget__form contact_form" method="POST" action="{{ route('create-user') }}">
                        @csrf

                        <div class="form-group">
                            <input
                            
                                type="text"
                                class="form-control widget__form-input"
                                placeholder="Full Name*"
                                name="name"
                                value="{{ old('name') }}"
                            >
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

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

                          <div class="form-group">
                            <input
                                type="password"
                                class="form-control widget__form-input"
                                placeholder="Confirm Password*"
                                name="confirm_password"
                            >
                            @error('confirm_password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                     <div class="widget__form-controls form-group">
    <div class="widget__form-controls-checkbox">
        <input
            type="checkbox"
            class="widget__form-controls-input"
            id="terms"
            name="terms"
            required
        >

        <label class="widget__form-controls-label" for="terms">
            Agree to our
            <a href="{{ route('terms') }}" class="widget__form-link">Terms & Conditions and Privacy Policy</a>
        </label>
    </div>

    @error('terms')
        <small class="text-danger d-block">{{ $message }}</small>
    @enderror
</div>

                        <div class="widget__form-btn">
                            <button type="submit" class="btn-custom">Sign Up Now</button>
                        </div>

                        <p class="widget__form-text">
                            Already have an account?
                            <a href="{{ route('login') }}" class="widget__form-link">Login</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- newsletter -->


@endsection
