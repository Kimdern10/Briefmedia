@extends('layouts.app')

@php
    $postUrl = route('posts.show', $post);
    $postTitle = $post->title;
    // The summary describes the story best; otherwise the start of the story
    $postDescription = Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($post->excerpt ?: $post->content)))), 160);
    $postRobots = ($post->no_index ? 'noindex' : 'index').', '.($post->no_follow ? 'nofollow' : 'follow')
        .($post->no_index ? '' : ', max-image-preview:large, max-snippet:-1');
    $postImage = $post->shareImageUrl();
    $postImageType = str_ends_with(strtolower($postImage), '.png') ? 'image/png' : (str_ends_with(strtolower($postImage), '.webp') ? 'image/webp' : 'image/jpeg');
    $placeholderImage = route('theme.asset', ['img', 'bm-placeholder.svg']);
    $authorName = $post->authorDisplayName();
    $authorFallbackPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($authorName) . '&background=e4407f&color=fff&size=120';
    // A typed-in writer's name gets an initial avatar; otherwise use the posting admin's photo
    $authorPhoto = trim((string) $post->author_name) !== ''
        ? $authorFallbackPhoto
        : (optional(optional($post->admin)->Userprofile)->photoUrl() ?? $authorFallbackPhoto);
    $videoEmbedUrl = $post->videoEmbedUrl();
    $extraImages = collect([$post->featured_image_3, $post->featured_image_4])->filter();
    $poll = $post->poll;
    $hasLiveCoverage = $post->isLive() || $post->liveUpdates()->exists();

    // Article body, with the "in the middle" ad after the 3rd paragraph when one is switched on
    $articleBody = $post->formattedContent();
    if ($middleAd = trim(view('snippets.site.ad-slot', ['placement' => 'article_middle'])->render())) {
        $articleBody = \App\Models\AdSlot::insertAfterParagraph($articleBody, $middleAd);
    }
@endphp

@section('meta_tags')

<title>{{ $post->title }} | {{ $globalSeo->site_name ?? config('app.name') }}</title>

<meta name="description" content="{{ $postDescription }}">
<meta name="robots" content="{{ $postRobots }}">
<link rel="canonical" href="{{ $postUrl }}">

{{-- The cover photo is the biggest thing on the page: start downloading it straight away --}}
@if($post->featured_image_1)
@if($coverSrcset = $post->coverSrcset())
<link rel="preload" as="image" href="{{ $post->coverUrl() }}" imagesrcset="{{ $coverSrcset }}" imagesizes="(max-width: 900px) 100vw, 1200px" fetchpriority="high">
@else
<link rel="preload" as="image" href="{{ $post->coverUrl() }}" fetchpriority="high">
@endif
@endif

<meta property="og:title" content="{{ $postTitle }}">
<meta property="og:description" content="{{ $postDescription }}">
<meta property="og:type" content="article">
<meta property="og:url" content="{{ $postUrl }}">
<meta property="og:site_name" content="{{ $globalSeo->site_name ?? config('app.name') }}">
<meta property="og:locale" content="en_NG">

<meta property="og:image" content="{{ $postImage }}">
<meta property="og:image:secure_url" content="{{ $postImage }}">
<meta property="og:image:type" content="{{ $postImageType }}">
@if($postImageSize = $post->shareImageSize())
<meta property="og:image:width" content="{{ $postImageSize[0] }}">
<meta property="og:image:height" content="{{ $postImageSize[1] }}">
@endif
<meta property="og:image:alt" content="{{ $postTitle }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $postTitle }}">
<meta name="twitter:description" content="{{ $postDescription }}">
<meta name="twitter:image" content="{{ $postImage }}">

<meta property="article:published_time" content="{{ $post->created_at->toIso8601String() }}">
<meta property="article:modified_time" content="{{ $post->lastModifiedAt()->toIso8601String() }}">
@foreach($post->tags as $metaTag)
<meta property="article:tag" content="{{ $metaTag->name }}">
@endforeach

{{-- Google News: NewsArticle (or LiveBlogPosting) structured data. A custom schema saved on the post wins. --}}
<script type="application/ld+json">{!! $post->customSchemaMarkup() ?: json_encode($post->newsArticleSchema(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>

{{-- Breadcrumb shown under the result in Google: BriefMedia › Category › Story --}}
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => array_values(array_filter([
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        $post->category ? ['@type' => 'ListItem', 'position' => 2, 'name' => $post->category->name, 'item' => route('category.page', $post->category->slug)] : null,
        ['@type' => 'ListItem', 'position' => $post->category ? 3 : 2, 'name' => $post->title, 'item' => $postUrl],
    ])),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>

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
                @if($post->isLive())
                    <a href="#live-updates" class="bm-chip bm-chip--live"><span class="bm-live__dot"></span> Live</a>
                @endif
                @if($post->isBreaking())
                    <span class="bm-chip bm-chip--breaking">Breaking</span>
                @endif
                <a href="{{ route('category.page', $post->category->slug) }}" class="bm-chip">{{ $post->category->name }}</a>
                @if($videoEmbedUrl)
                    <span class="bm-chip bm-chip--video"><i class="bi bi-play-fill"></i> Video</span>
                @endif
            @endif

            <h1 class="bm-article__title">{{ $post->title }}</h1>

            @if($post->excerpt)
                <p class="bm-article__dek">{{ $post->excerpt }}</p>
            @endif

            <div class="bm-meta">
                <span class="bm-byline">
                    <img class="bm-byline__avatar" src="{{ $authorPhoto }}" alt="{{ $authorName }}"
                         onerror="this.onerror=null;this.src='{{ $authorFallbackPhoto }}'">
                    <span>Posted by <a href="{{ $post->authorUrl() }}" class="bm-author-link"><strong>{{ $authorName }}</strong></a></span>
                </span>
                <span>{{ $post->created_at->format('F j, Y') }}</span>
                @if($post->wasUpdated())
                    <span class="bm-updated" title="{{ $post->content_updated_at->format('F j, Y g:i A') }}">
                        <i class="bi bi-arrow-repeat"></i> Updated {{ $post->content_updated_at->diffForHumans() }}
                    </span>
                @endif
                <span><i class="bi bi-clock"></i> {{ $post->reading_time }}</span>
                <span><i class="bi bi-eye"></i> {{ number_format($post->views ?? 0) }} views</span>
            </div>
        </header>

        <!-- ================= HERO IMAGE ================= -->
        <figure class="bm-article__hero">
            <img src="{{ $post->coverUrl() }}" fetchpriority="high" decoding="async"
                 @if($coverSrcset = $post->coverSrcset()) srcset="{{ $coverSrcset }}" sizes="(max-width: 900px) 100vw, 1200px" @endif
                 alt="{{ $post->title }}"
                 onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
        </figure>
        @if($post->image_caption || $post->image_credit)
            <p class="bm-photo-caption">
                @if($post->image_caption)<span>{{ $post->image_caption }}</span>@endif
                @if($post->image_credit)<span class="bm-photo-caption__credit"><i class="bi bi-camera"></i> Photo: {{ $post->image_credit }}</span>@endif
            </p>
        @endif

        @include('snippets.site.ad-slot', ['placement' => 'article_top'])

        <!-- ================= BODY ================= -->
        <div class="bm-article-layout">

            <!-- Engagement rail -->
            <aside class="bm-rail" aria-label="Post actions">
                <span class="bm-rail__label">Share</span>
                @include('snippets.site.like', ['post' => $post, 'class' => 'bm-icon-btn'])
                <livewire:bookmark-button :post="$post" />
                <a href="#comments" class="bm-icon-btn" aria-label="Jump to comments"><i class="bi bi-chat"></i></a>
                <span class="bm-rail__count">{{ $post->comments_count }}</span>
                @include('snippets.site.share', ['post' => $post, 'trigger' => 'bm-icon-btn', 'down' => true])
            </aside>

            <article>
                @if($videoEmbedUrl)
                    <div class="bm-video">
                        <iframe src="{{ $videoEmbedUrl }}" title="{{ $post->title }}" loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                @endif

                @if($post->featured_image_2)
                    <figure class="bm-figure" style="margin-top: 0;">
                        <img src="{{ \App\Models\Post::photoUrl($post->featured_image_2) }}" alt="{{ $post->title }}" loading="lazy"
                             onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                    </figure>
                @endif

                @if($post->election)
                    <livewire:election-results :election="$post->election" />
                @endif

                @if($hasLiveCoverage)
                    <livewire:live-updates :post="$post" />
                @endif

                <div class="bm-prose">
                    {!! $articleBody !!}
                </div>

                @if($poll)
                    <livewire:post-poll :poll="$poll" />
                @endif

                @if($post->correction_note)
                    <aside class="bm-correction">
                        <strong><i class="bi bi-pencil-square"></i> Correction</strong>
                        <p>{{ $post->correction_note }}</p>
                    </aside>
                @endif

                @if($post->tags->isNotEmpty())
                    <nav class="bm-tags" aria-label="Topics">
                        @foreach($post->tags as $postTag)
                            <a href="{{ route('tag.show', $postTag) }}" class="bm-tag">#{{ $postTag->name }}</a>
                        @endforeach
                    </nav>
                @endif

                @if($extraImages->isNotEmpty())
                    <div class="bm-gallery {{ $extraImages->count() === 1 ? 'bm-gallery--single' : '' }}">
                        @foreach($extraImages as $extraImage)
                            <img src="{{ \App\Models\Post::photoUrl($extraImage) }}" alt="{{ $post->title }}" loading="lazy"
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
                        <livewire:bookmark-button :post="$post" variant="pill" />
                    </div>
                </div>

                @include('snippets.site.ad-slot', ['placement' => 'article_bottom'])

                <!-- ================= COMMENTS ================= -->
                <livewire:post-comments :post="$post" />
            </article>

            <!-- ================= SIDEBAR ================= -->
            <aside class="bm-sidebar">
                @include('snippets.site.ad-slot', ['placement' => 'sidebar'])

                @php $sidebarMostRead = \App\Models\Post::mostReadToday(5)->reject(fn ($p) => $p->id === $post->id); @endphp
                @if($sidebarMostRead->isNotEmpty())
                    <div class="bm-widget">
                        <h5 class="bm-widget__title"><i class="bi bi-graph-up-arrow"></i> Most read today</h5>
                        <ol class="bm-mini-list" style="list-style: none; margin: 0; padding: 0;">
                            @foreach($sidebarMostRead as $readPost)
                                <li style="display: grid; grid-template-columns: 26px minmax(0, 1fr); gap: 8px; align-items: start;">
                                    <span class="bm-most-read__rank" style="font-size: 22px;">{{ $loop->iteration }}</span>
                                    <div>
                                        <p class="bm-mini__title"><a href="{{ route('posts.show', $readPost) }}">{{ $readPost->title }}</a></p>
                                        <div class="bm-meta"><span>{{ number_format($readPost->views_today) }} {{ Str::plural('read', $readPost->views_today) }} today</span></div>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif

                <div class="bm-widget">
                    <h5 class="bm-widget__title"><i class="bi bi-lightning-charge-fill"></i> Latest posts</h5>
                    <ul class="bm-mini-list">
                        @forelse ($latestPosts->take(5) as $latestPost)
                            <li class="bm-mini">
                                <a href="{{ route('posts.show', $latestPost) }}">
                                    <img class="bm-mini__img" loading="lazy" alt="{{ $latestPost->title }}"
                                         src="{{ $latestPost->coverUrl('card') }}"
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
