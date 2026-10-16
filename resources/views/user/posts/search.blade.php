@extends('layouts.app')

@section('title', 'Search: ' . $query . ' | ' . ($globalSeo->site_name ?? config('app.name')))

@section('content')
<main>
    <section class="bm-page-hero">
        <div class="bm-container">
            <nav class="bm-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <i class="bi bi-chevron-right" style="font-size: 10px;"></i>
                <span>Search</span>
            </nav>
            <h1 class="bm-page-hero__title">Results for <em>“{{ $query }}”</em></h1>
            <div class="bm-page-hero__stats">
                <span><strong>{{ $posts->total() }}</strong> {{ Str::plural('post', $posts->total()) }} found</span>
            </div>

            <form action="{{ route('user.posts.search') }}" method="GET" class="bm-subscribe"
                  style="max-width: 560px; margin-top: 24px; background: var(--bm-surface); border-color: var(--bm-line);">
                <input type="search" name="query" value="{{ $query }}" placeholder="Search again…" required aria-label="Search posts"
                       style="color: var(--bm-ink);">
                <button type="submit" class="bm-btn bm-btn--primary"><i class="bi bi-search"></i> Search</button>
            </form>
        </div>
    </section>

    <section class="bm-section">
        <div class="bm-container">
            @if($posts->isNotEmpty())
                <div class="bm-grid bm-grid--3">
                    @foreach ($posts as $post)
                        <div class="bm-reveal">
                            @include('snippets.site.post-card', ['post' => $post, 'variant' => 'default'])
                        </div>
                    @endforeach
                </div>

                {{ $posts->withQueryString()->links('snippets.site.pagination') }}
            @else
                <div class="bm-empty">
                    <i class="bi bi-search"></i>
                    <h3>No posts found</h3>
                    <p>We couldn't find anything matching “{{ $query }}”. Try a different keyword.</p>
                    <a href="{{ url('/') }}" class="bm-btn bm-btn--primary">Go to homepage</a>
                </div>
            @endif
        </div>
    </section>
</main>
@endsection
