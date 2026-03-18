<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Email Verification - BriefMedia</title>

<style>
    body {
        margin: 0;
        padding: 0;
        background-color: #f4f6f9;
        font-family: 'Segoe UI', Arial, sans-serif;
    }

    .wrapper {
        width: 100%;
        padding: 40px 15px;
        background-color: #f4f6f9;
    }

    .email-container {
        max-width: 600px;
        margin: auto;
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.05);
    }

    .header {
        background: linear-gradient(135deg, #0b7bcc, #0056b3);
        padding: 30px;
        text-align: center;
        color: #ffffff;
    }

    .header img {
        max-height: 60px;
        margin-bottom: 15px;
    }

    .header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
    }

    .body-content {
        padding: 35px 30px;
        color: #444;
        font-size: 16px;
        line-height: 1.7;
    }

    .verification-box {
        background: #f1f8ff;
        border: 2px dashed #0b7bcc;
        padding: 18px;
        text-align: center;
        font-size: 28px;
        font-weight: bold;
        letter-spacing: 5px;
        color: #0b7bcc;
        border-radius: 8px;
        margin: 25px 0;
    }

    .note {
        font-size: 14px;
        color: #777;
        margin-top: 15px;
    }

    .footer {
        background-color: #fafafa;
        padding: 20px;
        text-align: center;
        font-size: 13px;
        color: #888;
    }

    @media (max-width: 600px) {
        .body-content {
            padding: 25px 20px;
        }
    }
</style>
</head>

<body>
<div class="wrapper">

    <div class="email-container">

        <!-- HEADER -->
        <div class="header">
            <a href="{{ url('/') }}">
                <img src="{{ asset('assets/img/logo/ChatGPT_Image.png') }}" alt="BriefMedia Logo">
            </a>
            <h2>Email Verification</h2>
        </div>

        <!-- BODY -->
        <div class="body-content">

            <p>Hello 👋,</p>

            <p>
                Welcome to <strong>BriefMedia</strong> — your trusted source for 
                trending stories, deep insights, and global updates.
            </p>

            <p>
                To complete your registration, please use the verification code below:
            </p>

            <div class="verification-box">
                {{ $otp_code }}
            </div>

            <p>
                This code will expire in <strong>{{ $expiresIn ?? '5 minutes' }}</strong>.
            </p>

            <p class="note">
                If you did not create this account, you can safely ignore this email.
            </p>

            <p>
                We’re excited to have you join our growing community 🚀
            </p>

        </div>

        <!-- FOOTER -->
        <div class="footer">
            © {{ date('Y') }} BriefMedia. All rights reserved.<br>
            You’re receiving this email because you signed up on our website.
        </div>

    </div>

</div>
</body>
</html>