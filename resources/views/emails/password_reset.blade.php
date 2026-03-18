<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>BriefMedia Password Reset</title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<style>
body {
    margin:0;
    padding:0;
    background:#f4f6f9;
    font-family:'Segoe UI', Arial, sans-serif;
}

.wrapper {
    width:100%;
    padding:40px 15px;
}

.container {
    max-width:600px;
    margin:auto;
    background:#ffffff;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 8px 25px rgba(0,0,0,0.05);
}

.header {
    background:linear-gradient(135deg,#0b7bcc,#0056b3);
    padding:30px;
    text-align:center;
    color:#ffffff;
}

.header img {
    max-height:60px;
    margin-bottom:12px;
}

.header .brand {
    font-size:20px;
    font-weight:600;
}

.header .sub {
    font-size:13px;
    opacity:0.9;
    margin-top:4px;
}

.content {
    padding:35px 30px;
    color:#444;
    font-size:16px;
    line-height:1.7;
}

.content h1 {
    margin:0 0 15px 0;
    font-size:22px;
    color:#222;
}

.otp-box {
    background:#f1f8ff;
    border:2px dashed #0b7bcc;
    border-radius:10px;
    padding:20px;
    text-align:center;
    margin:25px 0;
}

.otp {
    font-size:30px;
    font-weight:bold;
    letter-spacing:6px;
    color:#0b7bcc;
    margin:0;
}

.small {
    font-size:14px;
    color:#777;
    margin-top:10px;
}

.footer {
    background:#fafafa;
    padding:20px;
    text-align:center;
    font-size:13px;
    color:#888;
}

a {
    color:#0b7bcc;
    text-decoration:none;
}

@media (max-width:600px) {
    .content {
        padding:25px 20px;
    }
    .otp {
        font-size:24px;
    }
}
</style>
</head>

<body>
<div class="wrapper">

    <div class="container">

        <!-- HEADER -->
        <div class="header">
            <a href="{{ url('/') }}">
                <img src="{{ asset('assets/img/logo/ChatGPT_Image.png') }}" alt="BriefMedia Logo">
            </a>
            <div class="brand">BriefMedia</div>
            <div class="sub">Account Security Team</div>
        </div>

        <!-- CONTENT -->
        <div class="content">

            <h1>Password Reset Code</h1>

            <p>
                Hi{{ isset($name) ? ' ' . e($name) : '' }}, 👋
            </p>

            <p>
                We received a request to reset your <strong>BriefMedia</strong> account password.
                Please use the secure code below to continue.
            </p>

            <div class="otp-box">
                <p class="otp">{{ $code ?? $body ?? '------' }}</p>
                <p class="small">
                    This code expires in <strong>{{ $expiresIn ?? '5 minutes' }}</strong>.<br>
                    Do not share this code with anyone.
                </p>
            </div>

            <p>
                If you didn’t request this reset, you can safely ignore this email.
                Your account remains secure.
            </p>

            <p class="small">
                Need help? Contact us at 
                <a href="mailto:{{ $supportEmail ?? 'support@briefMedia.com' }}">
                    {{ $supportEmail ?? 'support@briefMedia.com' }}
                </a>
            </p>

        </div>

        <!-- FOOTER -->
        <div class="footer">
            © {{ date('Y') }} BriefMedia. All rights reserved.<br>
            You’re receiving this email because a password reset was requested.
        </div>

    </div>

</div>
</body>
</html>