@extends('layouts.app')

@section('content')
<main class="main">
    <!-- Simple Header -->
    <div class="container-fluid mt-130">
        <div class="row">
            <div class="col-12 mb-10">
                <h2>Search Results for "{{ $query }}"</h2>
                <p class="text-muted">Found {{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}</p>
                <hr>
            </div>
        </div>
    </div>

    <!-- Search Results Grid -->
    <section class="blog-grid">
        <div class="container-fluid">
            <div class="row">
                @forelse ($posts as $post)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="post-card post-card--default">
                            <!-- Featured Image -->
                            <div class="post-card__image">
                                <a href="{{ route('posts.show', $post) }}">
                                    <img src="{{ asset('storage/' . ($post->featured_image_1 ?? 'default.jpg')) }}" 
                                         alt="{{ $post->title }}">
                                </a>
                            </div>

                            <div class="post-card__content">
                                <a href="{{ route('category.page', $post->category->slug) }}" class="category">{{ $post->category->name }}</a>
                                <h4 class="post-card__title">
                                    <a href="{{ route('posts.show', $post) }}" class="post-card__title-link">{{ $post->title }}</a>
                                </h4>
                                <p class="post-card__exerpt">{{ Str::limit($post->excerpt, 100) }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center py-5">
                            <i class="bi bi-search display-1 d-block mb-3"></i>
                            <h4>No posts found</h4>
                            <p>We couldn't find any posts matching "{{ $query }}".</p>
                            <a href="{{ url('/') }}" class="btn btn-primary mt-3">Go to Homepage</a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($posts->hasPages())
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-center">
                            {{ $posts->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</main>

<!-- Image styling -->
<style>
.post-card__image {
    width: 100%;
    aspect-ratio: 18/9;
    overflow: hidden;
    position: relative;
}

.post-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 0.3s ease;
}

.post-card__image:hover img {
    transform: scale(1.05);
}
</style>
@endsection