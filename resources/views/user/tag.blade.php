@extends('layouts.app')

@section('title', $tag->name . ' news | ' . ($globalSeo->site_name ?? config('app.name')))

@section('meta_tags')
<title>{{ $tag->name }} news | {{ $globalSeo->site_name ?? config('app.name') }}</title>
<meta name="description" content="Latest {{ $tag->name }} news and stories from {{ $globalSeo->site_name ?? config('app.name') }}.">
<link rel="canonical" href="{{ route('tag.show', $tag) }}{{ $posts->currentPage() > 1 ? '?page=' . $posts->currentPage() : '' }}">
<meta property="og:title" content="{{ $tag->name }} news">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ route('tag.show', $tag) }}">
@endsection

@section('content')
<main>
    <section class="bm-page-hero">
        <div class="bm-container">
            <nav class="bm-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <i class="bi bi-chevron-right" style="font-size: 10px;"></i>
                <span>Topics</span>
            </nav>
            <h1 class="bm-page-hero__title"><span class="bm-tag-hash">#</span>{{ $tag->name }}</h1>
            <p class="bm-page-hero__text">Every story we've published on {{ $tag->name }}, newest first.</p>
            <div class="bm-page-hero__stats">
                <span><strong>{{ number_format($posts->total()) }}</strong> {{ Str::plural('story', $posts->total()) }}</span>
            </div>
            @if($relatedTags->isNotEmpty())
                <div class="bm-tags" style="margin-top: 16px;">
                    @foreach($relatedTags as $relatedTag)
                        <a href="{{ route('tag.show', $relatedTag) }}" class="bm-tag">#{{ $relatedTag->name }}</a>
                    @endforeach
                </div>
            @endif
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

                {{ $posts->links('snippets.site.pagination') }}
            @else
                <div class="bm-empty">
                    <i class="bi bi-hash"></i>
                    <h3>No published stories on {{ $tag->name }} yet</h3>
                    <a href="{{ url('/') }}" class="bm-btn bm-btn--primary">Go to homepage</a>
                </div>
            @endif
        </div>
    </section>
</main>
@endsection
