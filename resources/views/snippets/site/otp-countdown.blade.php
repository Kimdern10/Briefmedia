{{--
    Countdown until a one-time code expires; enables the "Resend code" link afterwards.
    @param string   $email
    @param int|null $otpExpiresAt  Unix timestamp (seconds)
    @param string   $expiredText
--}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const countdownEl = document.getElementById('countdown-timer');
    const resendBtn = document.getElementById('resend-btn');
    const expiryTimestamp = {{ $otpExpiresAt ?? 'null' }} * 1000;
    const localKey = @json('otp-expiry-' . $email);

    const enableResend = () => {
        resendBtn.style.pointerEvents = 'auto';
        resendBtn.style.opacity = '1';
    };

    if (!expiryTimestamp) {
        countdownEl.textContent = '';
        enableResend();
        return;
    }

    try { localStorage.setItem(localKey, expiryTimestamp); } catch (e) {}

    function updateCountdown() {
        let expiresAt = expiryTimestamp;
        try { expiresAt = parseInt(localStorage.getItem(localKey), 10) || expiryTimestamp; } catch (e) {}
        const diff = expiresAt - Date.now();

        if (diff <= 0) {
            countdownEl.textContent = @json($expiredText);
            enableResend();
            return;
        }

        const mins = Math.floor(diff / 60000);
        const secs = Math.floor((diff % 60000) / 1000);
        countdownEl.textContent = `Expires in ${mins}:${String(secs).padStart(2, '0')}`;
        setTimeout(updateCountdown, 1000);
    }

    updateCountdown();
});
</script>
