@extends('layouts.app')

@section('content')
@php
    $placeholderImage = route('theme.asset', ['img', 'bm-placeholder.svg']);
    // Small 640px version for thumbnails and Trending; the big slider photo uses the full one
    $imageFor = fn ($post, $size = 'card') => $post->coverUrl($size);

    // Trending: posts an admin marked with the Trend button (Post::trendingNow)
    $filteredTrending = collect($trendingPosts ?? [])->take(8);
    $mostReadToday ??= \App\Models\Post::mostReadToday(5);
@endphp

{{-- The first slider photo is the biggest thing on the page: start downloading it straight away --}}
@php
    // Slider photo width: the screen less its side margins on phones and tablets (so most phones take the
    // 640px photo), about two-thirds of the page on laptops
    $heroSizes = '(max-width: 900px) 88vw, 760px';
@endphp
@if(($firstSlide = $sliderPosts->first()) && $firstSlide->featured_image_1)
    @push('head')
        @if($firstSrcset = $firstSlide->coverSrcset())
            <link rel="preload" as="image" href="{{ $firstSlide->coverUrl() }}" imagesrcset="{{ $firstSrcset }}" imagesizes="{{ $heroSizes }}" fetchpriority="high">
        @else
            <link rel="preload" as="image" href="{{ $firstSlide->coverUrl() }}" fetchpriority="high">
        @endif
    @endpush
@endif

<main>
    {{-- Main heading for search engines and screen readers (the design shows the logo instead) --}}
    <h1 class="bm-visually-hidden">{{ $globalSeo->site_name ?? config('app.name') }}: latest news, politics, sports and entertainment</h1>

    <!-- ================= FEATURED (rotating hero) ================= -->
    @if($sliderPosts->isNotEmpty())
        <section class="bm-hero" data-hero data-delay="6500">
            <div class="bm-container">
                <div class="bm-hero__grid">

                    <div class="bm-hero__stage" aria-roledescription="carousel" aria-label="Featured stories">
                        @foreach($sliderPosts as $index => $post)
                            <article class="bm-hero__slide {{ $index === 0 ? 'is-active' : '' }}" aria-roledescription="slide"
                                     aria-label="{{ $index + 1 }} of {{ $sliderPosts->count() }}">
                                {{-- Only the first photo downloads with the page; bm-site.js fills in the others once it has loaded --}}
                                @php $srcset = $post->coverSrcset(); @endphp
                                <img class="bm-hero__img" alt="{{ $post->title }}" decoding="async"
                                     @if($index === 0)
                                         src="{{ $imageFor($post, 'full') }}" fetchpriority="high"
                                         @if($srcset) srcset="{{ $srcset }}" sizes="{{ $heroSizes }}" @endif
                                     @else
                                         data-src="{{ $imageFor($post, 'full') }}"
                                         @if($srcset) data-srcset="{{ $srcset }}" sizes="{{ $heroSizes }}" @endif
                                     @endif
                                     onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                                <div class="bm-hero__content">
                                    @if($post->isLive())
                                        <span class="bm-chip bm-chip--live"><span class="bm-live__dot"></span> Live</span>
                                    @endif
                                    @if($post->isBreaking())
                                        <span class="bm-chip bm-chip--breaking">Breaking</span>
                                    @elseif($post->isHeadline())
                                        <span class="bm-chip bm-chip--light">Headline</span>
                                    @endif
                                    @if($post->category)
                                        <a href="{{ route('category.page', $post->category->slug) }}" class="bm-chip bm-chip--light">{{ $post->category->name }}</a>
                                    @endif
                                    @if($post->video_url)
                                        <span class="bm-chip bm-chip--light"><i class="bi bi-play-fill"></i> Video</span>
                                    @endif
                                    <h2 class="bm-hero__title">
                                        <a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
                                    </h2>
                                    @if($post->excerpt)
                                        <p class="bm-hero__excerpt">{{ Str::limit($post->excerpt, 180) }}</p>
                                    @endif
                                    <div class="bm-meta">
                                        <span>Posted by <a href="{{ $post->authorUrl() }}" class="bm-author-link"><strong>{{ $post->authorDisplayName() }}</strong></a></span>
                                        <span>{{ $post->created_at->format('F j, Y') }}</span>
                                        <span><i class="bi bi-clock"></i> {{ $post->reading_time }}</span>
                                        <span><i class="bi bi-chat"></i> {{ $post->comments_count ?? 0 }}</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach

                        @if($sliderPosts->count() > 1)
                            <div class="bm-hero__dots" role="tablist">
                                @foreach($sliderPosts as $index => $post)
                                    <button type="button" class="bm-hero__dot {{ $index === 0 ? 'is-active' : '' }}" data-hero-go="{{ $index }}"
                                            role="tab" aria-selected="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Show story {{ $index + 1 }}"></button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="bm-hero__list">
                        @foreach($sliderPosts as $index => $post)
                            <button type="button" class="bm-hero__item {{ $index === 0 ? 'is-active' : '' }}" data-hero-go="{{ $index }}">
                                <img class="bm-hero__thumb" src="{{ $imageFor($post) }}" alt="" loading="lazy"
                                     onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                                <span>
                                    <span class="bm-hero__item-cat">{{ optional($post->category)->name ?? 'Story' }}</span>
                                    <span class="bm-hero__item-title">{{ $post->title }}</span>
                                    <span class="bm-posted-by">Posted by <strong>{{ $post->authorDisplayName() }}</strong></span>
                                </span>
                                <span class="bm-hero__progress"></span>
                            </button>
                        @endforeach
                    </div>

                </div>
            </div>
        </section>
    @else
        <section class="bm-section">
            <div class="bm-container">
                <div class="bm-empty">
                    <i class="bi bi-journal-text"></i>
                    <h3>No stories yet</h3>
                    <p>Published posts will appear here.</p>
                </div>
            </div>
        </section>
    @endif

    <!-- ================= TRENDING ================= -->
    @if($filteredTrending->isNotEmpty())
        <section class="bm-section">
            <div class="bm-container">
                <div class="bm-section__head">
                    <div>
                        <span class="bm-section__eyebrow"><i class="bi bi-fire"></i> Hot right now</span>
                        <h2 class="bm-section__title">Trending</h2>
                    </div>
                </div>

                <div class="bm-trending">
                    @foreach($filteredTrending as $index => $trending)
                        <article class="bm-trend bm-reveal" style="transition-delay: {{ ($index % 4) * 70 }}ms;">
                            <a href="{{ route('posts.show', $trending) }}" class="bm-trend__media">
                                <img src="{{ $imageFor($trending) }}" alt="{{ $trending->title }}" loading="lazy"
                                     onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                                <span class="bm-trend__rank">{{ $index + 1 }}</span>
                                @if($trending->trend_pinned ?? false)
                                    <span class="bm-trend__featured">🔥 Featured</span>
                                @endif
                            </a>
                            <div>
                                <div class="bm-meta" style="margin-bottom: 6px;">
                                    @if($trending->category)
                                        <a href="{{ route('category.page', $trending->category->slug) }}" class="bm-chip">{{ $trending->category->name }}</a>
                                    @endif
                                </div>
                                <h3 class="bm-trend__title">
                                    <a href="{{ route('posts.show', $trending) }}">{{ Str::limit($trending->title, 80) }}</a>
                                </h3>
                                <p class="bm-posted-by" style="margin-top: 6px !important;">Posted by <a href="{{ $trending->authorUrl() }}" class="bm-author-link"><strong>{{ $trending->authorDisplayName() }}</strong></a></p>
                                <div class="bm-meta" style="margin-top: 8px;">
                                    <span><i class="bi bi-eye"></i> {{ number_format($trending->views ?? 0) }}</span>
                                    <span><i class="bi bi-heart"></i> {{ $trending->trend_likes ?? 0 }}</span>
                                    <span><i class="bi bi-chat"></i> {{ $trending->trend_comments ?? 0 }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($filteredTrending->count() < 8)
                    <p class="bm-trending-empty">Showing {{ $filteredTrending->count() }} trending {{ Str::plural('story', $filteredTrending->count()) }}</p>
                @endif
            </div>
        </section>
    @endif

    <!-- ================= MOST READ TODAY ================= -->
    @if($mostReadToday->isNotEmpty())
        <section class="bm-section bm-section--tight">
            <div class="bm-container">
                <div class="bm-section__head">
                    <div>
                        <span class="bm-section__eyebrow"><i class="bi bi-graph-up-arrow"></i> {{ now()->format('l, j F') }}</span>
                        <h2 class="bm-section__title">Most read today</h2>
                    </div>
                </div>
                <ol class="bm-most-read" style="list-style: none; margin: 0; padding: 0;">
                    @foreach($mostReadToday as $readPost)
                        <li class="bm-most-read__item">
                            <span class="bm-most-read__rank">{{ $loop->iteration }}</span>
                            <div>
                                <a href="{{ route('posts.show', $readPost) }}" class="bm-most-read__title">{{ $readPost->title }}</a>
                                <span class="bm-most-read__meta">
                                    {{ optional($readPost->category)->name ?? 'Story' }} · {{ number_format($readPost->views_today) }} {{ Str::plural('read', $readPost->views_today) }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    <!-- ================= CATEGORY SECTIONS ================= -->
    {{-- The first blocks come with the page; the rest load as the reader scrolls towards them,
         so phones lay out a much smaller page at first (each block is ~200 elements) --}}
    @php $sectionsWithPage = \App\Http\Controllers\UserController::HOME_SECTIONS_WITH_PAGE; @endphp
    @foreach ($categoriesWithPosts->take($sectionsWithPage) as $category)
        @include('snippets.site.home-category-section', [
            'category' => $category,
            'sectionNumber' => $loop->iteration,
            'tight' => ! ($loop->first && $filteredTrending->isEmpty()),
        ])

        @if($loop->iteration === 2)
            <div class="bm-container">@include('snippets.site.ad-slot', ['placement' => 'home_between'])</div>
        @endif
    @endforeach

    @if($categoriesWithPosts->count() > $sectionsWithPage)
        {{-- Replaced by the next blocks; until then (and without JavaScript) it links to each section --}}
        <div class="bm-container bm-home-more" data-home-more="{{ route('home', ['part' => 'more']) }}">
            <nav class="bm-section bm-section--tight" aria-label="More sections">
                <span class="bm-section__eyebrow">More sections</span>
                <p class="bm-meta" style="margin-top: 8px;">
                    @foreach($categoriesWithPosts->slice($sectionsWithPage) as $category)
                        <a href="{{ route('category.page', $category->slug) }}" class="bm-chip">{{ $category->name }}</a>
                    @endforeach
                </p>
            </nav>
        </div>
    @endif

</main>
@endsection
