@extends('layouts.app')

@php
    $postUrl = url()->current();
    $postTitle = $post->title;
    $postDescription = Str::limit(strip_tags($post->content ?? $post->excerpt), 160);
    $postImage = asset('storage/' . ($post->featured_image_1 ?? 'default.jpg'));
    $placeholderImage = asset('assets/img/bm-placeholder.svg');
    $authorName = optional($post->admin)->name ?? 'BriefMedia';
    $authorFallbackPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($authorName) . '&background=e4407f&color=fff&size=120';
    $authorProfilePicture = optional(optional($post->admin)->Userprofile)->profile_picture;
    $authorPhoto = $authorProfilePicture ? asset('Userprofile/' . $authorProfilePicture) : $authorFallbackPhoto;
    $extraImages = collect([$post->featured_image_3, $post->featured_image_4])->filter();
@endphp

@section('meta_tags')

<title>{{ $post->title }}</title>

<meta name="description" content="{{ $postDescription }}">
<link rel="canonical" href="{{ $postUrl }}">

<meta property="og:title" content="{{ $postTitle }}">
<meta property="og:description" content="{{ $postDescription }}">
<meta property="og:type" content="article">
<meta property="og:url" content="{{ $postUrl }}">
<meta property="og:site_name" content="BriefMedia">
<meta property="og:locale" content="en_US">

<meta property="og:image" content="{{ $postImage }}">
<meta property="og:image:secure_url" content="{{ $postImage }}">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $postTitle }}">
<meta name="twitter:description" content="{{ $postDescription }}">
<meta name="twitter:image" content="{{ $postImage }}">

<meta property="article:published_time" content="{{ $post->created_at->toIso8601String() }}">
<meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}">

@endsection

@section('content')
<main>
    <div class="bm-container">

        <!-- ================= ARTICLE HEADER ================= -->
        <header class="bm-article-head">
            <nav class="bm-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <i class="bi bi-chevron-right" style="font-size: 10px;"></i>
                @if($post->category)
                    <a href="{{ route('category.page', $post->category->slug) }}">{{ $post->category->name }}</a>
                @endif
            </nav>

            @if($post->category)
                <a href="{{ route('category.page', $post->category->slug) }}" class="bm-chip">{{ $post->category->name }}</a>
            @endif

            <h1 class="bm-article__title">{{ $post->title }}</h1>

            @if($post->excerpt)
                <p class="bm-article__dek">{{ $post->excerpt }}</p>
            @endif

            <div class="bm-meta">
                <span class="bm-byline">
                    <img class="bm-byline__avatar" src="{{ $authorPhoto }}" alt="{{ $authorName }}"
                         onerror="this.onerror=null;this.src='{{ $authorFallbackPhoto }}'">
                    <span>Posted by <strong>{{ $authorName }}</strong></span>
                </span>
                <span>{{ $post->created_at->format('F j, Y') }}</span>
                <span><i class="bi bi-clock"></i> {{ $post->reading_time }}</span>
                <span><i class="bi bi-eye"></i> {{ number_format($post->views ?? 0) }} views</span>
            </div>
        </header>

        <!-- ================= HERO IMAGE ================= -->
        <figure class="bm-article__hero">
            <img src="{{ $post->featured_image_1 ? asset('storage/' . $post->featured_image_1) : $placeholderImage }}"
                 alt="{{ $post->title }}"
                 onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
        </figure>

        <!-- ================= BODY ================= -->
        <div class="bm-article-layout">

            <!-- Engagement rail -->
            <aside class="bm-rail" aria-label="Post actions">
                <span class="bm-rail__label">Share</span>
                @include('snippets.site.like', ['post' => $post, 'class' => 'bm-icon-btn'])
                <a href="#comments" class="bm-icon-btn" aria-label="Jump to comments"><i class="bi bi-chat"></i></a>
                <span class="bm-rail__count">{{ $post->comments_count }}</span>
                @include('snippets.site.share', ['post' => $post, 'trigger' => 'bm-icon-btn', 'down' => true])
            </aside>

            <article>
                @if($post->featured_image_2)
                    <figure class="bm-figure" style="margin-top: 0;">
                        <img src="{{ asset('storage/' . $post->featured_image_2) }}" alt="{{ $post->title }}" loading="lazy"
                             onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                    </figure>
                @endif

                <div class="bm-prose">
                    {!! $post->content !!}
                </div>

                @if($extraImages->isNotEmpty())
                    <div class="bm-gallery {{ $extraImages->count() === 1 ? 'bm-gallery--single' : '' }}">
                        @foreach($extraImages as $extraImage)
                            <img src="{{ asset('storage/' . $extraImage) }}" alt="{{ $post->title }}" loading="lazy"
                                 onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                        @endforeach
                    </div>
                @endif

                <!-- Share bar -->
                <div class="bm-share-bar">
                    <span class="bm-share-bar__label">Enjoyed this story? Share it.</span>
                    <div class="bm-share-bar__links">
                        <a class="bm-btn bm-btn--ghost bm-btn--sm" target="_blank" rel="noopener"
                           href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($postUrl) }}"><i class="bi bi-facebook"></i> Facebook</a>
                        <a class="bm-btn bm-btn--ghost bm-btn--sm" target="_blank" rel="noopener"
                           href="https://twitter.com/intent/tweet?url={{ urlencode($postUrl) }}&text={{ urlencode($postTitle) }}"><i class="bi bi-twitter-x"></i> X</a>
                        <a class="bm-btn bm-btn--ghost bm-btn--sm" target="_blank" rel="noopener"
                           href="https://www.linkedin.com/sharing/share-offsite?url={{ urlencode($postUrl) }}"><i class="bi bi-linkedin"></i> LinkedIn</a>
                        <a class="bm-btn bm-btn--ghost bm-btn--sm" target="_blank" rel="noopener"
                           href="https://wa.me/?text={{ urlencode($postTitle . ' ' . $postUrl) }}"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                        <button type="button" class="bm-btn bm-btn--primary bm-btn--sm" data-copy-link="{{ $postUrl }}"><i class="bi bi-link-45deg"></i> Copy link</button>
                    </div>
                </div>

                <!-- ================= COMMENTS ================= -->
                <section class="bm-comments" id="comments">
                    <h2 class="bm-comments__title">Comments <span>({{ $post->comments_count }})</span></h2>

                    @auth
                        <form class="bm-comment-form" action="{{ route('posts.comment', $post) }}" method="POST">
                            @csrf
                            <label for="comment-content" class="bm-visually-hidden">Your comment</label>
                            <textarea id="comment-content" name="content" placeholder="Share your thoughts…" maxlength="2000" required>{{ old('content') }}</textarea>
                            @error('content')
                                <span class="bm-error">{{ $message }}</span>
                            @enderror
                            <div class="bm-comment-form__foot">
                                <small>Commenting as <strong>{{ auth()->user()->name }}</strong></small>
                                <button type="submit" class="bm-btn bm-btn--primary bm-btn--sm">Post comment</button>
                            </div>
                        </form>
                    @else
                        <div class="bm-comment-form bm-login-prompt">
                            <span>Join the conversation. Log in to leave a comment.</span>
                            <a href="{{ route('login') }}" class="bm-btn bm-btn--primary bm-btn--sm">Log in to comment</a>
                        </div>
                    @endauth

                    @forelse($post->comments as $comment)
                        <div class="bm-comment">
                            <span class="bm-comment__avatar">{{ strtoupper(substr(optional($comment->user)->name ?? 'G', 0, 1)) }}</span>
                            <div>
                                <div class="bm-comment__head">
                                    <span class="bm-comment__name">{{ optional($comment->user)->name ?? 'Guest' }}</span>
                                    <span class="bm-comment__time">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="bm-comment__text">{{ $comment->content }}</p>

                                @auth
                                    <details class="bm-reply">
                                        <summary><i class="bi bi-reply"></i> Reply</summary>
                                        <form action="{{ route('posts.comment', $post) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                            <textarea name="content" class="bm-textarea" placeholder="Write a reply…" maxlength="2000" required></textarea>
                                            <div class="bm-comment-form__foot">
                                                <span></span>
                                                <button type="submit" class="bm-btn bm-btn--primary bm-btn--sm">Reply</button>
                                            </div>
                                        </form>
                                    </details>
                                @endauth

                                @if($comment->replies->isNotEmpty())
                                    <div class="bm-comment__replies">
                                        @foreach($comment->replies as $reply)
                                            <div class="bm-comment">
                                                <span class="bm-comment__avatar">{{ strtoupper(substr(optional($reply->user)->name ?? 'G', 0, 1)) }}</span>
                                                <div>
                                                    <div class="bm-comment__head">
                                                        <span class="bm-comment__name">{{ optional($reply->user)->name ?? 'Guest' }}</span>
                                                        <span class="bm-comment__time">{{ $reply->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    <p class="bm-comment__text">{{ $reply->content }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="bm-meta" style="padding: 8px 0;">No comments yet. Be the first to share your thoughts.</p>
                    @endforelse
                </section>
            </article>

            <!-- ================= SIDEBAR ================= -->
            <aside class="bm-sidebar">
                <div class="bm-widget">
                    <h5 class="bm-widget__title"><i class="bi bi-lightning-charge-fill"></i> Latest posts</h5>
                    <ul class="bm-mini-list">
                        @forelse ($latestPosts->take(5) as $latestPost)
                            <li class="bm-mini">
                                <a href="{{ route('posts.show', $latestPost) }}">
                                    <img class="bm-mini__img" loading="lazy" alt="{{ $latestPost->title }}"
                                         src="{{ $latestPost->featured_image_1 ? asset('storage/' . $latestPost->featured_image_1) : $placeholderImage }}"
                                         onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                                </a>
                                <div>
                                    <p class="bm-mini__title"><a href="{{ route('posts.show', $latestPost) }}">{{ $latestPost->title }}</a></p>
                                    <div class="bm-meta">
                                        <span>{{ $latestPost->created_at->format('M j, Y') }}</span>
                                        @if($latestPost->created_at->diffInDays(now()) < 7)
                                            <span class="bm-badge-new">New</span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="bm-meta">No latest posts available</li>
                        @endforelse
                    </ul>
                </div>
            </aside>
        </div>

        <!-- ================= RELATED ================= -->
        @if(isset($relatedPosts) && $relatedPosts->isNotEmpty())
            <section class="bm-related">
                <div class="bm-section__head">
                    <div>
                        <span class="bm-section__eyebrow">Keep reading</span>
                        <h2 class="bm-section__title">More in {{ optional($post->category)->name ?? 'this section' }}</h2>
                    </div>
                    @if($post->category)
                        <a href="{{ route('category.page', $post->category->slug) }}" class="bm-link-arrow">View all <i class="bi bi-arrow-right"></i></a>
                    @endif
                </div>
                <div class="bm-grid bm-grid--3">
                    @foreach($relatedPosts->take(3) as $relatedPost)
                        <div class="bm-reveal">
                            @include('snippets.site.post-card', [
                                'post' => $relatedPost,
                                'variant' => 'default',
                                'categoryName' => optional($post->category)->name,
                                'categorySlug' => optional($post->category)->slug,
                            ])
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</main>
@endsection
