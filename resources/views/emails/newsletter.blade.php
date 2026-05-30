<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BriefMedia Newsletter</title>
</head>

<body style="font-family:Arial,sans-serif; background:#f4f6f8; padding:20px; margin:0;">

<div style="max-width:600px; margin:auto; background:#fff; padding:30px; border-radius:12px;">

    <!-- Header -->
    <div style="text-align:center; margin-bottom:30px;">
     <img src="{{ config('app.url') . '/assets/img/logo/ChatGPT_Image.png' }}"
     alt="BriefMedia Logo"
     width="150">
        <h1 style="color:#0b7bcc;">BriefMedia Newsletter</h1>
        <p>Your weekly dose of trending posts</p>
    </div>

    <!-- Latest Posts -->
    <h3 style="color:#333;">Latest Posts</h3>

    @foreach($latestPosts as $post)
        <div style="margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">
            <a href="{{ url('posts/'.$post->slug) }}" style="font-size:18px; font-weight:600; text-decoration:none;">
                {{ $post->title }}
            </a>

            <p style="color:#555;">
                {{ \Illuminate\Support\Str::limit(strip_tags($post->content), 120) }}
            </p>

            <a href="{{ url('posts/'.$post->slug) }}" style="color:#0b7bcc;">Read More →</a>
        </div>
    @endforeach

    <!-- Trending Posts -->
    <h3 style="color:#333; margin-top:30px;">Trending Posts</h3>

    @foreach($trendingPosts as $post)
        <div style="margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">
            <a href="{{ url('posts/'.$post->slug) }}" style="font-size:18px; font-weight:600; text-decoration:none;">
                {{ $post->title }}
            </a>

            <p style="color:#555;">
                {{ \Illuminate\Support\Str::limit(strip_tags($post->content), 120) }}
            </p>

            <a href="{{ url('posts/'.$post->slug) }}" style="color:#0b7bcc;">Read More →</a>
        </div>
    @endforeach

    <!-- Footer -->
    <div style="text-align:center; font-size:12px; color:#777; margin-top:30px;">
        <p>
            Unsubscribe:
            <a href="{{ route('newsletter.unsubscribe', $subscriber->unsubscribe_token) }}">
                Click here
            </a>
        </p>

        <p>&copy; {{ date('Y') }} BriefMedia</p>
    </div>

</div>

</body>
</html>