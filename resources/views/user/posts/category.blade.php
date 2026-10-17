@extends('layouts.app')

@section('title', $category->name . ' | ' . ($globalSeo->site_name ?? config('app.name')))

@section('content')
@php
    $placeholderImage = route('theme.asset', ['img', 'bm-placeholder.svg']);
    $sidebarCategories = $allCategories ?? collect();
    $sidebarLatest = ($latestPosts ?? collect())->take(5);
    $sidebarTrending = ($trendingPosts ?? collect())->take(5);
@endphp

<main>
    <!-- ================= PAGE HERO ================= -->
    <section class="bm-page-hero">
        <div class="bm-container">
            <nav class="bm-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <i class="bi bi-chevron-right" style="font-size: 10px;"></i>
                <span>{{ $category->name }}</span>
            </nav>
            <h1 class="bm-page-hero__title">{{ $category->name }}</h1>
            <p class="bm-page-hero__text">{{ $category->description ?: 'The latest stories, updates and analysis from ' . $category->name . '.' }}</p>
            <div class="bm-page-hero__stats">
                <span><strong>{{ $posts->total() }}</strong> {{ Str::plural('story', $posts->total()) }}</span>
                @if($posts->isNotEmpty())
                    <span>Updated <strong>{{ $posts->first()->created_at->diffForHumans() }}</strong></span>
                @endif
            </div>
        </div>
    </section>

    <section class="bm-section">
        <div class="bm-container bm-layout">

            <!-- ================= POSTS ================= -->
            <div>
                @if($posts->isNotEmpty())
                    <div class="bm-grid">
                        @foreach ($posts as $post)
                            <div class="bm-reveal" style="{{ $loop->first && $posts->onFirstPage() ? 'grid-column: 1 / -1;' : '' }}">
                                @include('snippets.site.post-card', [
                                    'post' => $post,
                                    'variant' => $loop->first && $posts->onFirstPage() ? 'lead' : 'default',
                                    'categoryName' => $category->name,
                                    'categorySlug' => $category->slug,
                                ])
                            </div>
                        @endforeach
                    </div>

                    {{ $posts->links('snippets.site.pagination') }}
                @else
                    <div class="bm-empty">
                        <i class="bi bi-journal-x"></i>
                        <h3>No posts in this category yet</h3>
                        <p>Check back soon, new stories are on the way.</p>
                        <a href="{{ url('/') }}" class="bm-btn bm-btn--primary">Back to homepage</a>
                    </div>
                @endif
            </div>

            <!-- ================= SIDEBAR ================= -->
            <aside class="bm-sidebar">
                @include('snippets.site.ad-slot', ['placement' => 'sidebar'])

                @if($sidebarCategories->isNotEmpty())
                    <div class="bm-widget">
                        <h5 class="bm-widget__title"><i class="bi bi-grid"></i> Categories</h5>
                        <ul class="bm-cat-list">
                            @foreach ($sidebarCategories as $cat)
                                <li>
                                    <a href="{{ route('category.page', $cat->slug) }}" class="{{ $cat->id === $category->id ? 'is-active' : '' }}">
                                        {{ $cat->name }} <span>{{ $cat->posts_count ?? 0 }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($sidebarLatest->isNotEmpty())
                    <div class="bm-widget">
                        <h5 class="bm-widget__title"><i class="bi bi-lightning-charge-fill"></i> Latest posts</h5>
                        <ul class="bm-mini-list">
                            @foreach ($sidebarLatest as $latestPost)
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
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($sidebarTrending->isNotEmpty())
                    <div class="bm-widget">
                        <h5 class="bm-widget__title"><i class="bi bi-fire"></i> Trending</h5>
                        <ul class="bm-mini-list">
                            @foreach ($sidebarTrending as $trendingPost)
                                <li class="bm-mini">
                                    <a href="{{ route('posts.show', $trendingPost) }}">
                                        <img class="bm-mini__img" loading="lazy" alt="{{ $trendingPost->title }}"
                                             src="{{ $trendingPost->featured_image_1 ? asset('storage/' . $trendingPost->featured_image_1) : $placeholderImage }}"
                                             onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                                    </a>
                                    <div>
                                        <p class="bm-mini__title"><a href="{{ route('posts.show', $trendingPost) }}">{{ $trendingPost->title }}</a></p>
                                        <div class="bm-meta">
                                            <span><i class="bi bi-eye"></i> {{ number_format($trendingPost->views) }}</span>
                                            @if($trendingPost->views > 100)
                                                <span class="bm-badge-new">Hot</span>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>

        </div>
    </section>
</main>
@endsection
