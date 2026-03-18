@extends('layouts.app')

@php
    $postUrl = url()->current();
    $postTitle = $post->title;
    $postDescription = Str::limit(strip_tags($post->content ?? $post->excerpt), 160);
    $postImage = asset('storage/' . ($post->featured_image_1 ?? 'default.jpg'));
@endphp

@section('meta_tags')
    <!-- Open Graph / Facebook / WhatsApp / LinkedIn -->
    <meta property="og:title" content="{{ $postTitle }}" />
    <meta property="og:description" content="{{ $postDescription }}" />
    <meta property="og:image" content="{{ $postImage }}" />
    <meta property="og:url" content="{{ $postUrl }}" />
    <meta property="og:type" content="article" />
    <meta property="og:site_name" content="BriefMedia" />
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $postTitle }}" />
    <meta name="twitter:description" content="{{ $postDescription }}" />
    <meta name="twitter:image" content="{{ $postImage }}" />
    
    <!-- Optional: Add more meta tags for better SEO -->
    <meta name="description" content="{{ $postDescription }}" />
    <meta name="author" content="{{ $post->admin->name ?? 'BriefMedia' }}" />
    
    <!-- Article Meta -->
    <meta property="article:published_time" content="{{ $post->created_at->toIso8601String() }}" />
    <meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}" />
    <meta property="article:section" content="{{ $post->category->name ?? 'Article' }}" />
@endsection

@section('content')
<style>
/* ===== Hero Image ===== */
.post-hero {
    position: relative;
    width: 100%;
    height: 600px;
    overflow: hidden;
    border-radius: 15px;
}

.post-hero-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ===== Excerpt Styling ===== */
.post-single__excerpt {
    font-size: 18px;
    font-style: italic;
    line-height: 1.6;
    padding: 15px 0;
    border-left: 4px solid #007bff;
    padding-left: 15px;
}

/* ===== Make Full Content Same Style As Excerpt ===== */
.post-single__body {
    font-size: 18px;
    font-style: italic;
    line-height: 1.6;
    padding: 15px 0;
    border-left: 4px solid #007bff;
    padding-left: 15px;
}

/* Ensure paragraphs inside content inherit properly */
.post-single__body p {
    margin-bottom: 15px;
}

/* Secondary Images */
.post-main-image {
    width: 100%;
    height: 450px;
    object-fit: cover;
    border-radius: 12px;
}

.post-gallery-image {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-radius: 12px;
}

/* ===== MINIMAL SHARE STYLES - LIKE ALPHATIMES.NG ===== */
.post-share-minimal {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 40px 0 30px;
    padding: 15px 0;
    border-top: 1px solid #eaeaea;
    border-bottom: 1px solid #eaeaea;
}

.share-minimal-label {
    color: #999;
    font-size: 14px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.share-minimal-links {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 20px;
}

.share-minimal-link {
    color: #666;
    font-size: 14px;
    text-decoration: none;
    transition: color 0.2s ease;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    font-family: inherit;
    position: relative;
}

.share-minimal-link:hover {
    color: #ff69b4;
}

/* Divider between links */
.share-minimal-link:not(:last-child)::after {
    content: '';
    display: inline-block;
    width: 1px;
    height: 12px;
    background-color: #ddd;
    margin-left: 20px;
    vertical-align: middle;
}

/* Copy link button specific */
.copy-minimal-btn {
    position: relative;
}

.copy-minimal-success {
    display: none;
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #333;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    margin-bottom: 8px;
    z-index: 10;
}

.copy-minimal-success::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #333;
}

/* HERO IMAGE */
.post-hero {
    width: 100%;
    max-height: 600px;       /* maximum height */
    overflow: hidden;
    border-radius: 15px;
    position: relative;
}

.post-hero-image {
    width: 100%;
    height: 190%;
    object-fit: cover;       /* ensures it fills area without distortion */
}

/* SECONDARY / MAIN IMAGE */
.post-main-image {
    width: 100%;
    max-height: 490px;       /* limit height */
    object-fit: cover;
    border-radius: 12px;
}

/* GALLERY IMAGES */
.post-gallery-image {
    width: 100%;
    max-height: 360px;       /* limit height */
    object-fit: cover;
    border-radius: 12px;
}

/* RESPONSIVE ADJUSTMENTS */
@media (max-width: 992px) {
    .post-hero {
        max-height: 400px;
    }
    .post-main-image {
        max-height: 300px;
    }
    .post-gallery-image {
        max-height: 200px;
    }
}

@media (max-width: 576px) {
    .post-hero {
        max-height: 300px;
    }
    .post-main-image {
        max-height: 200px;
    }
    .post-gallery-image {
        max-height: 150px;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .post-hero {
        height: 350px;
    }

    .post-single__excerpt,
    .post-single__body {
        font-size: 16px;
    }

    .post-main-image {
        height: 280px;
    }

    .post-gallery-image {
        height: 200px;
    }
    
    .post-share-minimal {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .share-minimal-links {
        gap: 15px;
    }
    
    .share-minimal-link:not(:last-child)::after {
        margin-left: 15px;
    }
}
</style>

<main class="main">
<section class="mt-130 mb-30">
<div class="container-fluid">
<div class="row">

<!-- ================= MAIN CONTENT ================= -->
<div class="col-xl-9 side-content">
<div class="theiaStickySidebar">
<div class="post-single">

<!-- HERO IMAGE -->
<div class="post-hero mb-4">
    <img src="{{ asset('storage/' . ($post->featured_image_1 ?? 'default.jpg')) }}" 
         alt="{{ $post->title }}" 
         class="post-hero-image">
</div>

<!-- POST HEADER -->
<div class="post-single__content">

    <a href="{{ route('category.page', $post->category->slug) }}" class="category">
        {{ $post->category->name }}
    </a>

    <h2 class="post-single__title">
        {{ $post->title }}
    </h2>

    <ul class="post-single__meta list-inline">
        <li class="post-single__meta-item">
            <span class="dot"></span> 
            {{ $post->created_at->format('F d, Y') }}
        </li>
        <li class="post-single__meta-item">
            <span class="dot"></span> 
            {{ $post->reading_time ?? 5 }} min Read
        </li>
    </ul>

    {{-- EXCERPT AFTER DATE --}}
    @if($post->excerpt)
        <div class="post-single__excerpt mt-3">
            {{ $post->excerpt }}
        </div>
    @endif

</div>

<!-- IMAGE 2 -->
@if($post->featured_image_2)
<div class="mt-4 mb-4">
    <img src="{{ asset('storage/' . $post->featured_image_2) }}" 
         alt="{{ $post->title }}" 
         class="post-main-image">
</div>
@endif

<!-- POST BODY -->
<div class="post-single__body">
    {!! $post->content !!}
</div>

<!-- IMAGE 3 & 4 -->
@if($post->featured_image_3 || $post->featured_image_4)
<div class="post-gallery mt-5">
    <div class="row">
        @if($post->featured_image_3)
        <div class="col-md-6 mb-4">
            <img src="{{ asset('storage/' . $post->featured_image_3) }}" 
                 alt="{{ $post->title }}" 
                 class="post-gallery-image">
        </div>
        @endif

        @if($post->featured_image_4)
        <div class="col-md-6 mb-4">
            <img src="{{ asset('storage/' . $post->featured_image_4) }}" 
                 alt="{{ $post->title }}" 
                 class="post-gallery-image">
        </div>
        @endif
    </div>
</div>
@endif

<!-- ========== MINIMAL SHARE SECTION - LIKE ALPHATIMES.NG ========== -->
<div class="post-share-minimal">
    <span class="share-minimal-label">Share:</span>
    <div class="share-minimal-links">
        <!-- Facebook -->
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($postUrl) }}" 
           target="_blank" 
           class="share-minimal-link">
            Facebook
        </a>
        
        <!-- X (Twitter) -->
        <a href="https://twitter.com/intent/tweet?url={{ urlencode($postUrl) }}&text={{ urlencode($postTitle) }}" 
           target="_blank" 
           class="share-minimal-link">
            X
        </a>
        
        <!-- LinkedIn -->
        <a href="https://www.linkedin.com/sharing/share-offsite?url={{ urlencode($postUrl) }}" 
           target="_blank" 
           class="share-minimal-link">
            LinkedIn
        </a>
        
        <!-- WhatsApp -->
        <a href="https://wa.me/?text={{ urlencode($postTitle . ' ' . $postUrl) }}" 
           target="_blank" 
           class="share-minimal-link">
            WhatsApp
        </a>
        
        <!-- Copy Link Button -->
        <button class="share-minimal-link copy-minimal-btn" onclick="copyPostLinkMinimal()">
            Copy link
            <span id="copy-minimal-success" class="copy-minimal-success">Copied!</span>
        </button>
    </div>
</div>

<!-- ========== COMMENTS SECTION ========== -->
<div class="widget mt-5">
    <h5 class="widget__title">
        Comments ({{ $post->comments_count }})
    </h5>

    @auth
    <form class="widget__form mt-4" 
          action="{{ route('posts.comment', $post) }}" 
          method="POST">
        @csrf
        <h5 class="widget__form-title">Leave a Reply</h5>
        <textarea name="content" 
                  class="form-control widget__form-input mb-3" 
                  rows="5" 
                  placeholder="Message*" 
                  required></textarea>
        <button type="submit" class="btn-custom">
            Send Comment
        </button>
    </form>
    @else
    <p class="mt-4">
        Please <a href="{{ route('login') }}">login</a> to comment.
    </p>
    @endauth

    <div class="comments mt-4">
        @foreach($post->comments as $comment)
        <div class="comment mb-3" 
             style="border-bottom:1px solid #ddd; padding-bottom:10px;">
            <strong>{{ $comment->user->name }}</strong>
            <p>{{ $comment->content }}</p>
            <small>{{ $comment->created_at->diffForHumans() }}</small>

            @auth
            <form action="{{ route('posts.comment', $post) }}" 
                  method="POST" 
                  style="margin-left:20px; margin-top:5px;">
                @csrf
                <input type="hidden" 
                       name="parent_id" 
                       value="{{ $comment->id }}">
                <textarea name="content" 
                          class="form-control mb-2" 
                          rows="2" 
                          placeholder="Reply..." 
                          required></textarea>
                <button type="submit" 
                        class="btn-custom btn-sm">
                    Reply
                </button>
            </form>
            @endauth

            @foreach($comment->replies as $reply)
            <div class="reply" 
                 style="margin-left:40px; margin-top:5px;">
                <strong>{{ $reply->user->name }}</strong>
                <p>{{ $reply->content }}</p>
                <small>{{ $reply->created_at->diffForHumans() }}</small>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>

</div>
</div>
</div>

<!-- ================= SIDEBAR ================= -->
<div class="col-xl-3 max-width side-sidebar">
<div class="theiaStickySidebar">

                 <!-- Latest Posts Widget -->
                       <div class="widget mt-4">
    <h5 class="widget__title">Latest Posts</h5>
    <ul class="widget__latest-posts">
        @php
            // Take only 5 latest posts
            $filteredLatest = $latestPosts->take(5);
        @endphp
        
        @forelse ($filteredLatest as $i => $post)
            <li class="widget__latest-posts__item">
                <div class="widget__latest-posts-image">
                    <a href="{{ route('posts.show', $post) }}" class="widget__latest-posts-link"> 
                        <img src="{{ asset('storage/' . ($post->featured_image_1 ?? 'default.jpg')) }}" alt="{{ $post->title }}" class="widget__latest-posts-img">
                    </a>
                </div>
                <div class="widget__latest-posts-count">{{ $i + 1 }}</div>
                <div class="widget__latest-posts__content">
                    <p class="widget__latest-posts-title">
                        <a href="{{ route('posts.show', $post) }}" class="widget__latest-posts-link">{{ $post->title }}</a>
                    </p>
                    <small class="widget__latest-posts-date">
                        <i class="bi bi-clock-fill widget__latest-posts-icon"></i>{{ $post->created_at->format('F d, Y') }}
                        
                        {{-- Optional: Show time ago for newer posts --}}
                        @if($post->created_at->diffInDays(now()) < 7)
                            <span class="badge bg-danger ms-2">New</span>
                        @endif
                    </small>
                </div>
            </li>
        @empty
            <li class="widget__latest-posts__item">
                <div class="widget__latest-posts__content">
                    <p class="widget__latest-posts-title text-muted">No latest posts available</p>
                </div>
            </li>
        @endforelse
        
        {{-- Optional: Show count if less than 5 posts are available --}}
        @if($filteredLatest->count() > 0 && $filteredLatest->count() < 5)
            <li class="widget__latest-posts__item">
                <small class="text-muted">
                    Showing {{ $filteredLatest->count() }} of 5 latest posts
                </small>
            </li>
        @endif
    </ul>
</div>

</div>
</div>

</div>
</div>
</section>
</main>

<!-- Share JavaScript -->
<script>
// Copy link function for minimal style
function copyPostLinkMinimal() {
    const url = "{{ url()->current() }}";
    
    navigator.clipboard.writeText(url).then(() => {
        const successMsg = document.getElementById('copy-minimal-success');
        successMsg.style.display = 'block';
        
        setTimeout(() => {
            successMsg.style.display = 'none';
        }, 2000);
    }).catch(() => {
        alert('Could not copy link. Please try again.');
    });
}

// Close copy success message when clicking elsewhere
document.addEventListener('click', function(event) {
    if (!event.target.closest('.copy-minimal-btn')) {
        const successMsg = document.getElementById('copy-minimal-success');
        if (successMsg) {
            successMsg.style.display = 'none';
        }
    }
});
</script>

<!-- Font Awesome (if not already included) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endsection