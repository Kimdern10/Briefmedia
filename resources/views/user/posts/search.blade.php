@extends('layouts.app')

@php
    $monthNames = [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    // "October 2026", "17 October 2026", "October" (any year) or "2026"
    $datePart = $day
        ? $day->format('j F Y')
        : trim(($month ? $monthNames[$month] : '').' '.($year ?: ''));

    $heading = match (true) {
        $query !== '' && $datePart !== '' => 'Results for “'.e($query).'” <span class="bm-search-when">in '.e($datePart).'</span>',
        $query !== '' => 'Results for <em>“'.e($query).'”</em>',
        $datePart !== '' => 'Stories from <em>'.e($datePart).'</em>',
        default => 'Search stories',
    };
    $pageTitle = $hasFilter ? 'Search: '.trim($query.' '.$datePart) : 'Search stories';
@endphp

@section('title', $pageTitle . ' | ' . ($globalSeo->site_name ?? config('app.name')))
{{-- Search results aren't pages Google should list (they'd compete with the stories) --}}
@section('robots', 'noindex, follow')

@section('content')
<main>
    <section class="bm-page-hero">
        <div class="bm-container">
            <nav class="bm-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <i class="bi bi-chevron-right" style="font-size: 10px;"></i>
                <span>Search</span>
            </nav>
            <h1 class="bm-page-hero__title">{!! $heading !!}</h1>
            @if($hasFilter)
                <div class="bm-page-hero__stats">
                    <span><strong>{{ number_format($posts->total()) }}</strong> {{ Str::plural('story', $posts->total()) }} found</span>
                </div>
            @endif

            <form action="{{ route('user.posts.search') }}" method="GET" class="bm-search-form" role="search">
                <label class="bm-search-form__field bm-search-form__field--query">
                    <span>Keyword</span>
                    <input type="search" name="query" value="{{ $query }}" placeholder="e.g. Soludo, ASUU, Super Eagles" aria-label="Keyword">
                </label>
                <label class="bm-search-form__field">
                    <span>Month</span>
                    <select name="month" aria-label="Month">
                        <option value="">Any month</option>
                        @foreach($monthNames as $number => $name)
                            <option value="{{ $number }}" @selected($month === $number)>{{ $name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="bm-search-form__field">
                    <span>Year</span>
                    <select name="year" aria-label="Year">
                        <option value="">Any year</option>
                        @foreach($years as $optionYear)
                            <option value="{{ $optionYear }}" @selected($year === $optionYear)>{{ $optionYear }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="bm-search-form__field">
                    <span>Or an exact day</span>
                    <input type="date" name="date" value="{{ $day?->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" aria-label="Exact day">
                </label>
                <div class="bm-search-form__actions">
                    <button type="submit" class="bm-btn bm-btn--primary"><i class="bi bi-search"></i> Search</button>
                    @if($hasFilter)
                        <a href="{{ route('user.posts.search') }}" class="bm-btn bm-btn--ghost">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </section>

    <section class="bm-section">
        <div class="bm-container">
            @if(! $hasFilter)
                <div class="bm-empty">
                    <i class="bi bi-calendar3"></i>
                    <h3>Find a story</h3>
                    <p>Type a word, pick a month and year, choose an exact day, or combine them.</p>
                </div>
            @elseif($posts->isNotEmpty())
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
                    <h3>No stories found</h3>
                    <p>
                        Nothing matches
                        @if($query !== '')“{{ $query }}”@endif
                        @if($datePart !== '') {{ $query !== '' ? 'in' : 'from' }} {{ $datePart }}@endif.
                        Try another word or date.
                    </p>
                    <a href="{{ route('user.posts.search') }}" class="bm-btn bm-btn--primary">Start a new search</a>
                </div>
            @endif
        </div>
    </section>
</main>
@endsection
