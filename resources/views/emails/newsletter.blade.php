<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BriefMedia Newsletter</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f8;
        padding: 20px;
        margin: 0;
    }
    .container {
        max-width: 600px;
        margin: auto;
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .header {
        text-align: center;
        margin-bottom: 30px;
    }
    .header img {
        height: 60px;
        margin-bottom: 10px;
    }
    .header h1 {
        color: #0b7bcc;
        font-size: 24px;
        margin: 0;
    }
    .header p {
        color: #555;
        font-size: 14px;
        margin-top: 5px;
    }
    .section-title {
        color: #0b7bcc;
        font-size: 20px;
        margin: 20px 0 10px;
        font-weight: 600;
    }
    .post {
        margin-bottom: 25px;
        border-bottom: 1px solid #eee;
        padding-bottom: 15px;
    }
    .post a.title {
        font-size: 18px;
        color: #222;
        font-weight: 600;
        text-decoration: none;
    }
    .post p.excerpt {
        color: #555;
        font-size: 14px;
        margin-top: 5px;
    }
    .post a.read-more {
        font-size: 13px;
        color: #0b7bcc;
        text-decoration: none;
    }
    .footer {
        border-top: 1px solid #eee;
        padding-top: 20px;
        text-align: center;
        font-size: 12px;
        color: #777;
    }
    .footer a {
        color: #0b7bcc;
        text-decoration: none;
    }
    @media only screen and (max-width: 620px) {
        .container {
            padding: 20px;
        }
        .header h1 {
            font-size: 20px;
        }
        .post a.title {
            font-size: 16px;
        }
        .section-title {
            font-size: 18px;
        }
    }
</style>
</head>
<body>
    <div class="container">

        <!-- Header -->
        <div class="header">
            <img src="{{ asset('assets/img/logo/ChatGPT_Image.png') }}" alt="BriefMedia Logo">
            <h1>BriefMedia Newsletter</h1>
            <p>Your weekly dose of trending posts & updates</p>
        </div>

        <!-- Trending Posts -->
        @if(isset($trendingPosts) && $trendingPosts->count())
            <div class="section-title">🔥 Trending This Week</div>
            @foreach($trendingPosts as $post)
            <div class="post">
                <a href="{{ url('posts/'.$post->slug) }}" class="title">
                    {{ $post->title ?? 'Untitled Post' }}
                </a>
                <p class="excerpt">
                    {{ \Illuminate\Support\Str::limit(strip_tags($post->body ?? ''), 120) }}
                </p>
                <a href="{{ url('posts/'.$post->slug) }}" class="read-more">Read More →</a>
            </div>
            @endforeach
        @endif

        <!-- Latest Posts -->
        @if(isset($latestPosts) && $latestPosts->count())
            <div class="section-title">🆕 Latest Posts</div>
            @foreach($latestPosts as $post)
            <div class="post">
                <a href="{{ url('posts/'.$post->slug) }}" class="title">
                    {{ $post->title ?? 'Untitled Post' }}
                </a>
                <p class="excerpt">
                    {{ \Illuminate\Support\Str::limit(strip_tags($post->body ?? ''), 120) }}
                </p>
                <a href="{{ url('posts/'.$post->slug) }}" class="read-more">Read More →</a>
            </div>
            @endforeach
        @endif

        <!-- Footer / Unsubscribe -->
        <div class="footer">
            <p>If you wish to unsubscribe from these emails, click 
                <a href="{{ route('newsletter.unsubscribe', $subscriber->unsubscribe_token) }}">here</a>.
            </p>
            <p>&copy; {{ date('Y') }} BriefMedia. All rights reserved.</p>
        </div>

    </div>
</body>
</html>