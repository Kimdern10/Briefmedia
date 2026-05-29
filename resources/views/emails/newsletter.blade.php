<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BerifMedia Newsletter</title>
</head>
<body style="font-family:Arial,sans-serif; background:#f4f6f8; padding:20px; margin:0;">
    <div style="max-width:600px; margin:auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.05);">

        <!-- Header -->
        <div style="text-align:center; margin-bottom:30px;">
            <img src="{{ asset('assets/img/logo/ChatGPT_Image.png') }}" alt="BerifMedia Logo" style="height:60px; margin-bottom:10px;">
            <h1 style="color:#0b7bcc; font-size:24px; margin:0;">BerifMedia Newsletter</h1>
            <p style="color:#555; font-size:14px; margin-top:5px;">Your weekly dose of trending posts & updates</p>
        </div>

        <!-- Posts -->
        @foreach($posts as $post)
            <div style="margin-bottom:25px; border-bottom:1px solid #eee; padding-bottom:15px;">
                <a href="{{ url('posts/'.$post->slug) }}" style="font-size:18px; color:#222; font-weight:600; text-decoration:none;">
                    {{ $post->title ?? 'Untitled Post' }}
                </a>
                <p style="color:#555; font-size:14px; margin-top:5px;">
                  {{ \Illuminate\Support\Str::limit(strip_tags($post->body ?? ''), 120) }}
                </p>
                <a href="{{ url('posts/'.$post->slug) }}" style="font-size:13px; color:#0b7bcc; text-decoration:none;">Read More →</a>
            </div>
        @endforeach

        <!-- Footer / Unsubscribe -->
        <div style="border-top:1px solid #eee; padding-top:20px; text-align:center; font-size:12px; color:#777;">
            <p>If you wish to unsubscribe from these emails, click 
                <a href="{{ route('newsletter.unsubscribe', $subscriber->unsubscribe_token) }}" style="color:#0b7bcc; text-decoration:none;">here</a>.
            </p>
            <p>&copy; {{ date('Y') }} BerifMedia. All rights reserved.</p>
        </div>

    </div>
</body>
</html>