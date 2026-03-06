@extends('layouts.app')

@section('content')

<!-- Confirm Code -->
<section class="m-top mb-60">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 m-auto">
                <div class="widget">
                    <h5 class="widget__title">Confirm Code</h5>

                    <p class="text-muted mb-3">
                        A verification code has been sent to your email. Enter it below.
                    </p>

                    <!-- Countdown -->
                    <div id="countdown-timer" class="text-danger fw-bold mb-3 text-center"></div>

                    <form action="{{ route('submitPasswordResetCode') }}" method="POST" class="widget__form">
                        @csrf

                        <!-- Hidden Email -->
                        <input type="hidden" name="user_email" value="{{ $email }}">

                        @error('user_email')
                            <small class="text-danger d-block mb-2">{{ $message }}</small>
                        @enderror

                        <!-- Code -->
                        <div class="form-group">
                            <input
                                type="text"
                                name="code"
                                class="form-control widget__form-input"
                                placeholder="Enter verification code*"
                                value="{{ old('code') }}"
                                required
                            >
                            @error('code')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="widget__form-btn d-flex justify-content-between align-items-center mt-3">
                            <button type="submit" class="btn-custom">
                                Verify Code
                            </button>

                            <a
                                href="{{ route('resend.code', ['email' => $email]) }}"
                                id="resend-btn"
                                class="widget__form-link"
                                style="pointer-events:none; opacity:.6"
                                onclick="localStorage.removeItem('otp-expiry-{{ $email }}')"
                            >
                                Resend Code
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Countdown Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const countdownEl = document.getElementById("countdown-timer");
    const resendBtn = document.getElementById("resend-btn");

    const expiryTimestamp = {{ $otpExpiresAt ?? 'null' }} * 1000;
    const localKey = "otp-expiry-{{ $email }}";

    if (!expiryTimestamp) {
        countdownEl.innerHTML = "Unable to start timer.";
        return;
    }

    localStorage.setItem(localKey, expiryTimestamp);

    function updateCountdown() {
        const now = Date.now();
        const expiresAt = parseInt(localStorage.getItem(localKey));
        const diff = expiresAt - now;

        if (diff <= 0) {
            countdownEl.textContent = "Code expired. You can now resend.";
            resendBtn.style.pointerEvents = "auto";
            resendBtn.style.opacity = "1";
            return;
        }

        const mins = Math.floor(diff / 60000);
        const secs = Math.floor((diff % 60000) / 1000);

        countdownEl.textContent = `Code expires in ${mins}m ${secs}s`;
        setTimeout(updateCountdown, 1000);
    }

    updateCountdown();
});
</script>

@endsection
