@extends('layouts.app')

@section('title', $writerName . ' | ' . ($globalSeo->site_name ?? config('app.name')))
@section('meta_description', 'Stories written by ' . $writerName . ' for ' . ($globalSeo->site_name ?? config('app.name')) . '.')

@section('content')
<main>
    <section class="bm-page-hero">
        <div class="bm-container bm-writer-hero">
            <img class="bm-writer-hero__photo" src="{{ $writerPhoto }}" alt="{{ $writerName }}"
                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($writerName) }}&background=e4407f&color=fff&size=240'">
            <div>
                <nav class="bm-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ url('/') }}">Home</a>
                    <i class="bi bi-chevron-right" style="font-size: 10px;"></i>
                    <span>Writers</span>
                </nav>
                <h1 class="bm-page-hero__title">{{ $writerName }}</h1>
                <p class="bm-page-hero__text">Stories written for BriefMedia by {{ $writerName }}.</p>
                <div class="bm-page-hero__stats">
                    <span><strong>{{ number_format($posts->total()) }}</strong> {{ Str::plural('story', $posts->total()) }}</span>
                    <span><strong>{{ number_format($totalViews) }}</strong> total views</span>
                </div>
            </div>
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
                    <i class="bi bi-pen"></i>
                    <h3>No published stories yet</h3>
                    <a href="{{ url('/') }}" class="bm-btn bm-btn--primary">Go to homepage</a>
                </div>
            @endif
        </div>
    </section>
</main>
@endsection
