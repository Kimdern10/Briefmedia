@extends('layouts.app')

@section('content')
@php
    $placeholderImage = route('theme.asset', ['img', 'bm-placeholder.svg']);
    $imageFor = fn ($post) => $post->featured_image_1 ? asset('storage/' . $post->featured_image_1) : $placeholderImage;

    // Trending: already ranked by Post::trendingNow() (views + likes x2 + comments x3)
    $filteredTrending = collect($trendingPosts ?? [])->take(8);
    $mostReadToday = \App\Models\Post::mostReadToday(5);
@endphp

<main>

    <!-- ================= FEATURED (rotating hero) ================= -->
    @if($sliderPosts->isNotEmpty())
        <section class="bm-hero" data-hero data-delay="6500">
            <div class="bm-container">
                <div class="bm-hero__grid">

                    <div class="bm-hero__stage" aria-roledescription="carousel" aria-label="Featured stories">
                        @foreach($sliderPosts as $index => $post)
                            <article class="bm-hero__slide {{ $index === 0 ? 'is-active' : '' }}" aria-roledescription="slide"
                                     aria-label="{{ $index + 1 }} of {{ $sliderPosts->count() }}">
                                <img class="bm-hero__img" src="{{ $imageFor($post) }}" alt="{{ $post->title }}"
                                     @if($index > 0) loading="lazy" @endif
                                     onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                                <div class="bm-hero__content">
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
    @foreach ($categoriesWithPosts as $category)
        @php
            $categoryPosts = $category->posts->take(6);
            $leadPost = $categoryPosts->first();
            $otherPosts = $categoryPosts->slice(1);
        @endphp

        <section class="bm-section {{ $loop->first && $filteredTrending->isEmpty() ? '' : 'bm-section--tight' }}">
            <div class="bm-container">
                <div class="bm-section__head">
                    <div>
                        <span class="bm-section__eyebrow">Section {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h2 class="bm-section__title">{{ $category->name }}</h2>
                    </div>
                    <a href="{{ route('category.page', $category->slug) }}" class="bm-link-arrow">
                        View all <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="bm-cat-grid {{ $otherPosts->isEmpty() ? 'bm-cat-grid--solo' : '' }}">
                    <div class="bm-reveal">
                        @include('snippets.site.post-card', [
                            'post' => $leadPost,
                            'variant' => 'lead',
                            'categoryName' => $category->name,
                            'categorySlug' => $category->slug,
                        ])
                    </div>

                    @if($otherPosts->isNotEmpty())
                        <div class="bm-cat-grid__rest">
                            @foreach($otherPosts as $post)
                                <div class="bm-reveal" style="transition-delay: {{ $loop->index * 60 }}ms;">
                                    @include('snippets.site.post-card', [
                                        'post' => $post,
                                        'variant' => 'compact',
                                        'categoryName' => $category->name,
                                        'categorySlug' => $category->slug,
                                    ])
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endforeach

</main>
@endsection
