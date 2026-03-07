
@extends('layouts.app')
@section('content')

<main class="main">

  <!-- ================= SLIDER ================= -->
<div class="slider slider--two">

    <!-- Main Slider -->
    <div class="swiper slider__top">
        <div class="swiper-wrapper">
            @foreach ($sliderPosts as $post)
            <div class="swiper-slide slider__item"
                  style="background-image: url('{{ asset('storage/' . ($post->featured_image_1 ?? 'default.jpg')) }}'); height: 800px;">
                
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-7 col-lg-9 col-md-12">
                            <div class="slider__item-content">
                                <a href="#" class="category">
                                    {{ $post->category->name ?? 'Uncategorized' }}
                                </a>

                                <h1 class="slider__title">
                                    <a href="{{ route('posts.show', $post) }}" class="slider__title-link">
                                        {{ $post->title }}
                                    </a>
                                </h1>

                                <p class="slider__exerpt">{{ $post->excerpt }}</p>

                                <ul class="slider__meta list-inline">
                                    <li class="slider__meta-item">
                                        <img src="{{ asset('storage/' . ($post->featured_image_1 ?? 'default.jpg')) }}" class="slider__meta-img">
                                    </li>
                                    <li class="slider__meta-item">{{ $post->admin->name ?? 'Admin' }}</li>
                                    <li class="slider__meta-item">
                                        <span class="dot"></span>
                                        {{ $post->created_at->format('F d, Y') }}
                                    </li>
                                    <li class="slider__meta-item">
                                        <span class="dot"></span>
                                        {{ $post->comments_count ?? 0 }} comments
                                    </li>
                                </ul>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
            @endforeach
        </div>

        <!-- Navigation -->
        <!-- <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div> -->

        <!-- Pagination
        <div class="swiper-pagination"></div> -->
    </div>

    <!-- Thumbnails Slider -->
    <div class="swiper slider__bottom thumbs-slider container-fluid">
        <div class="swiper-wrapper">
            @foreach ($sliderPosts as $post)
            <div class="swiper-slide">
                <div class="post-slider">
                    <img src="{{ asset('storage/' . ($post->featured_image_1 ?? 'default.jpg')) }}" class="post-slider__img">
                    <div class="post-slider__content">
                        <p class="post-slider__title"><span>{{ $post->title }}</span></p>
                        <ul class="post-slider__meta list-inline">
                            <li class="post-slider__meta-link">
                                <i class="bi bi-clock-fill"></i>
                                {{ $post->created_at->format('F d, Y') }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <style>
        @media (max-width: 768px) {
            .thumbs-slider {
                display: none !important;
            }
        }
    </style>

</div>
    <!-- ================= END SLIDER ================= -->

    <!-- ================= TRENDING NEWS SECTION ================= -->
  @if(isset($trendingPosts) && $trendingPosts->count() > 0)
    @php
        // Filter and sort posts by views and likes, then take 8
        $filteredTrending = $trendingPosts
            ->filter(function($post) {
                // Keep posts with views > 0 OR likes > 0
                return $post->views > 0 || ($post->likedByUsers && $post->likedByUsers->count() > 0);
            })
            ->sortByDesc(function($post) {
                // Sort by views first, then by likes count
                return $post->views;
            })
            ->values() // Reset keys after sorting
            ->take(8); // Take only 8 posts
    @endphp
    
    @if($filteredTrending->count() > 0)
    <section class="trending-news mt-90">
        <div class="container-fluid">
            <div class="section-header d-flex justify-content-between align-items-center mb-4">
                <h3 class="section-title">
                    <i class="bi bi-fire" style="color: #ff69b4;"></i> 
                    Trending Now
                </h3>
                <a href="" class="view-all-link">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            
            <div class="row">
                @foreach($filteredTrending as $index => $trending)
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="trending-card">
                        <div class="trending-card__image">
                            <a href="{{ route('posts.show', $trending) }}">
                                <img src="{{ asset('storage/' . ($trending->featured_image_1 ?? 'default.jpg')) }}" 
                                     alt="{{ $trending->title }}"
                                     class="trending-card__img">
                                <span class="trending-badge">{{ $index + 1 }}</span>
                            </a>
                        </div>
                        <div class="trending-card__content">
                            <a href="" class="trending-category">
                                {{ $trending->category->name ?? 'Uncategorized' }}
                            </a>
                            <h5 class="trending-card__title">
                                <a href="{{ route('posts.show', $trending) }}">{{ Str::limit($trending->title, 50) }}</a>
                            </h5>
                            <div class="trending-meta">
                                <span><i class="bi bi-eye"></i> {{ $trending->views ?? 0 }}</span>
                                <span><i class="bi bi-hand-thumbs-up"></i> {{ $trending->likedByUsers->count() ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Add a clearfix after every 4 posts to maintain grid layout --}}
                @if(($index + 1) % 4 == 0 && !$loop->last)
                <div class="w-100 d-none d-md-block"></div>
                @endif
                
                @endforeach
            </div>
            
            {{-- Optional: Show count if less than 8 posts are available --}}
            @if($filteredTrending->count() < 8)
            <div class="text-center mt-3">
                <small class="text-muted">
                    Showing {{ $filteredTrending->count() }} trending posts
                </small>
            </div>
            @endif
        </div>
    </section>
    @endif
@endif
    <!-- ================= END TRENDING NEWS ================= -->


    <!-- ================= CATEGORY SECTIONS ================= -->
 @foreach ($categoriesWithPosts as $category)
    <section class="mt-90">
        <div class="container-fluid">

            <!-- CATEGORY TITLE -->
            <div class="row mb-3">
                <div class="col-lg-12 d-flex justify-content-between align-items-center">
                    <h3 class="section-title text-uppercase fw-bold mb-0">
                       {{ $category->name }}
                    </h3>
                    
                    <!-- View All Link -->
                    <a href="{{ route('category.page', $category->slug) }}" 
                       class="view-all-link">
                        View All <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="col-12">
                    <hr class="mb-4">
                </div>
            </div>

            <!-- POSTS -->
            <div class="row">
               @foreach ($category->posts->take(6) as $post) <!-- ✅ limit 6 per category -->

                @php
                    $postUrl = route('posts.show', $post);
                    $postTitle = $post->title;
                @endphp
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="post-card post-card--default">

                        <div class="post-card__image">
                            <a href="{{ route('posts.show', $post) }}">
                                <img src="{{ asset('storage/' . ($post->featured_image_1 ?? 'default.jpg')) }}" 
                                     alt="{{ $post->title }}">
                            </a>
                        </div>

                        <div class="post-card__content">

                            <a href="" class="category">
                                {{ $post->category->name }}
                            </a>

                            <h5 class="post-card__title">
                                <a href="{{ route('posts.show', $post) }}"
                                   class="post-card__title-link">
                                    {{ $post->title }}
                                </a>
                            </h5>

                                                                <p class="post-card__exerpt">
    {{ Str::limit($post->excerpt, 120) }}
    <a href="{{ route('posts.show', $post) }}" class="read-more-inline">
        Read More →
    </a>
</p>

                            <!-- ================= POST META ================= -->
                            <ul class="post-card__meta list-inline d-flex flex-wrap align-items-center" style="gap: 0.5rem; margin:0; padding:0; list-style:none;">
                                <li class="post-card__meta-item">
                                    Posted by 
                                    <a href="#" class="post-card__meta-link">{{ $post->admin->name }}</a>
                                </li>
                                <li class="post-card__meta-item">
                                    <span class="dot"></span> {{ $post->created_at->format('F d, Y') }}
                                </li>
                                <li class="post-card__meta-item">
                                    <span class="dot"></span> {{ $post->reading_time ?? 5 }} min Read
                                </li>
                                <li class="post-card__meta-item">
                                    <i class="bi bi-eye-fill"></i> {{ $post->views ?? 0 }}
                                </li>
                                <li class="post-card__meta-item">
                                    <i class="bi bi-chat-left-text-fill"></i> {{ $post->comments_count ?? 0 }}
                                </li>
                                
                                <!-- ================= LIKE BUTTON ================= -->
                                <li class="post-card__meta-item" 
                                    x-data="{ 
                                        liked: {{ $post->likedByUsers->contains(auth()->id()) ? 'true' : 'false' }},
                                        count: {{ $post->likedByUsers->count() }}
                                    }">
                                    @auth
                                        <button @click="
                                            fetch('{{ route('posts.like', $post) }}', {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                }
                                            })
                                            .then(res => res.json())
                                            .then(data => {
                                                liked = data.liked;
                                                count = data.likes_count;
                                            })
                                        " class="btn btn-link like-btn p-0 border-0 text-decoration-none">
                                            <i :class="liked ? 'bi-hand-thumbs-up-fill text-primary' : 'bi-hand-thumbs-up'" class="bi"></i>
                                            <span x-text="liked ? 'Unlike' : 'Like'"></span>
                                            (<span x-text="count"></span>)
                                        </button>
                                    @else
                                        <a href="{{ route('login') }}" class="text-muted text-decoration-none">
                                            <i class="bi bi-hand-thumbs-up"></i> Like ({{ $post->likedByUsers->count() }})
                                        </a>
                                    @endauth
                                </li>
                                <!-- ================= END LIKE BUTTON ================= -->
                                
                                <!-- ================= SHARE BUTTON ================= -->
                                <li class="post-card__meta-item">
                                    <div class="share-minimal-dropdown">
                                        <button class="share-minimal-toggle" onclick="toggleMinimalShareMenu({{ $post->id }})">
                                            <i class="bi bi-share"></i> Share
                                        </button>
                                        <div id="share-minimal-menu-{{ $post->id }}" class="share-minimal-menu">
                                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($postUrl) }}" 
                                               target="_blank" class="share-minimal-option">
                                                Facebook
                                            </a>
                                            <a href="https://twitter.com/intent/tweet?url={{ urlencode($postUrl) }}&text={{ urlencode($postTitle) }}" 
                                               target="_blank" class="share-minimal-option">
                                                X
                                            </a>
                                            <a href="https://www.linkedin.com/sharing/share-offsite?url={{ urlencode($postUrl) }}" 
                                               target="_blank" class="share-minimal-option">
                                                LinkedIn
                                            </a>
                                            <a href="https://wa.me/?text={{ urlencode($postTitle . ' ' . $postUrl) }}" 
                                               target="_blank" class="share-minimal-option">
                                                WhatsApp
                                            </a>
                                            <button onclick="copyMinimalLink('{{ $postUrl }}', {{ $post->id }})" class="share-minimal-option copy-option">
                                                Copy link
                                                <span id="copy-minimal-success-{{ $post->id }}" class="copy-minimal-success">Copied!</span>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                                <!-- ================= END SHARE BUTTON ================= -->
                                
                            </ul>
                            <!-- ================= END POST META ================= -->

                        </div>
                    </div>
                </div>
                @endforeach
            </div>

         

        </div>
    </section>
    @endforeach
    <!-- ================= END CATEGORY SECTIONS ================= -->


    <!-- ================= NEWSLETTER ================= -->
    <!-- <section class="newslettre__section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6 col-md-10 col-sm-11 m-auto">
                    <div class="newslettre">

                        <div class="newslettre__info">
                            <h3 class="newslettre__title">
                                Get The Best Blog Stories into Your inbox!
                            </h3>
                            <p class="newslettre__desc">
                                Sign up for free and be the first to get notified about new posts.
                            </p>
                        </div>

                        <form action="#" class="newslettre__form">
                            @csrf
                            <input type="email"
                                   class="newslettre__form-input form-control"
                                   placeholder="Your email address"
                                   required>
                            <button class="newslettre__form-submit">
                                Subscribe
                            </button>
                        </form>

                        <ul class="list-inline social-media social-media--layout-three">
                            <li><a href="#"><i class="bi bi-facebook"></i>Facebook</a></li>
                            <li><a href="#"><i class="bi bi-instagram"></i>Instagram</a></li>
                            <li><a href="#"><i class="bi bi-twitter-x"></i>Twitter</a></li>
                            <li><a href="#"><i class="bi bi-youtube"></i>Youtube</a></li>
                        </ul>

                    </div>
                </div>
            </div>
        </div>
    </section> -->

</main>

<!-- ================= STYLES ================= -->
<style>
/* Trending Section Styles */
.trending-news {
    padding: 40px 0;
}

.section-header {
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 15px;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    color: #333;
}

.section-title i {
    margin-right: 8px;
}

.view-all-link {
    color: #ff69b4;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.view-all-link:hover {
    color: #ff4da6;
    transform: translateX(5px);
}

.trending-card {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    height: 100%;
}

.trending-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(255, 105, 180, 0.15);
}

.trending-card__image {
    position: relative;
    overflow: hidden;
    height: 200px;
}

.trending-card__img {
    width: 100%;
    height: 140%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.trending-card:hover .trending-card__img {
    transform: scale(1.05);
}

.trending-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #ff69b4;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.trending-card__content {
    padding: 15px;
}

.trending-category {
    color: #ff69b4;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-decoration: none;
    display: inline-block;
    margin-bottom: 8px;
}

.trending-card__title {
    font-size: 1rem;
    line-height: 1.4;
    margin-bottom: 10px;
}

.trending-card__title a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.trending-card__title a:hover {
    color: #ff69b4;
}

.trending-meta {
    display: flex;
    gap: 15px;
    font-size: 0.8rem;
    color: #999;
}

.trending-meta i {
    margin-right: 3px;
    color: #ff69b4;
}

/* Share Button Minimal Styles */
.share-minimal-dropdown {
    position: relative;
    display: inline-block;
}

.share-minimal-toggle {
    background: none;
    border: none;
    color: #ff69b4;
    font-size: 0.9rem;
    padding: 4px 0;
    cursor: pointer;
    transition: color 0.2s ease;
    display: flex;
    align-items: center;
    gap: 4px;
}

.share-minimal-toggle:hover {
    color: #ff4da6;
}

.share-minimal-toggle i {
    font-size: 1rem;
}

.share-minimal-menu {
    display: none;
    position: absolute;
    bottom: 100%;
    right: 0;
    background-color: white;
    border: 1px solid #eaeaea;
    border-radius: 8px;
    padding: 8px 0;
    min-width: 140px;
    box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
    z-index: 1000;
    margin-bottom: 5px;
}

.share-minimal-menu.show {
    display: block;
}

.share-minimal-option {
    display: block;
    padding: 8px 16px;
    color: #333;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    width: 100%;
    border: none;
    background: none;
    cursor: pointer;
    text-align: left;
    position: relative;
}

.share-minimal-option:hover {
    background-color: #f5f5f5;
    color: #ff69b4;
    padding-left: 20px;
}

.copy-option {
    position: relative;
}

.copy-minimal-success {
    display: none;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    top: -30px;
    background-color: #333;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    white-space: nowrap;
}

.share-minimal-dropdown {
    position: relative;
    display: inline-block;
}

.share-minimal-toggle {
    background: none;
    border: none;
    color: #ff69b4;
    font-size: 0.9rem;
    padding: 4px 0;
    cursor: pointer;
    transition: color 0.2s ease;
    display: flex;
    align-items: center;
    gap: 4px;
}

.share-minimal-toggle:hover {
    color: #ff4da6;
}

.share-minimal-toggle i {
    font-size: 1rem;
}

.share-minimal-menu {
    display: none;
    position: absolute;
    bottom: 100%;
    right: 0;
    background-color: white;
    border: 1px solid #eaeaea;
    border-radius: 8px;
    padding: 8px 0;
    min-width: 140px;
    box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
    z-index: 1000;
    margin-bottom: 5px;
}

.share-minimal-menu.show {
    display: block;
}

.post-card__image {
    width: 100%;
    aspect-ratio: 18/9; /* This creates a consistent aspect ratio */
    overflow: hidden;
    position: relative;
}

.post-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}
.share-minimal-option {
    display: block;
    padding: 8px 16px;
    color: #333;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    width: 100%;
    border: none;
    background: none;
    cursor: pointer;
    text-align: left;
    position: relative;
}

.share-minimal-option:hover {
    background-color: #f5f5f5;
    color: #ff69b4;
    padding-left: 20px;
}

.copy-option {
    position: relative;
}

.copy-minimal-success {
    display: none;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    top: -30px;
    background-color: #333;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    white-space: nowrap;
}

.post-card__image {
    width: 100%;
    height: 270px; /* Fixed height for all images */
    overflow: hidden;
    position: relative;
}

.post-card__image img {
    width: 100%;
    height: 140%;
    object-fit: cover; /* This ensures the image covers the area without distortion */
    object-position: center; /* Centers the image within the container */
    transition: transform 0.3s ease; /* Optional: smooth hover effect */
}

/* Optional: Add hover effect */
.post-card__image:hover img {
    transform: scale(1.05); /* Slight zoom on hover */
}

.copy-minimal-success::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #333;
}

.copy-minimal-success::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #333;
}

.read-more-inline{
    margin-left:5px;
    font-weight:600;
    color:#ff69b4;
    text-decoration:none;
}

.read-more-inline:hover{
    color:#ff4da6;
}

/* Dark theme support */
[data-theme="dark"] .section-title {
    color: #fff;
}

[data-theme="dark"] .trending-card {
    background: #1a1a1a;
    border: 1px solid #333;
}

[data-theme="dark"] .trending-card__title a {
    color: #fff;
}

[data-theme="dark"] .share-minimal-menu {
    background-color: #222;
    border-color: #444;
}

[data-theme="dark"] .share-minimal-option {
    color: #ccc;
}

[data-theme="dark"] .share-minimal-option:hover {
    background-color: #333;
}

@media (max-width: 768px) {
    .trending-news {
        padding: 30px 0;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 10px;
    }
    
    .trending-card__image {
        height: 160px;
    }
    
    .share-minimal-menu {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        margin-bottom: 0;
        border-radius: 15px 15px 0 0;
    }
    
    .share-minimal-option {
        padding: 15px 20px;
        text-align: center;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .share-minimal-menu {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        margin-bottom: 0;
        border-radius: 15px 15px 0 0;
        border: 1px solid #eaeaea;
        border-bottom: none;
        box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
    }
    
    .share-minimal-menu.show {
        display: block;
        animation: slideUp 0.3s ease;
    }
    
    .share-minimal-option {
        padding: 15px 20px;
        text-align: center;
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(100%);
        }
        to {
            transform: translateY(0);
        }
    }
/* huuceu */
    /* ================= RESPONSIVE IMAGE & TEXT ================= */
@media (max-width: 768px) {
    /* Slider text adjustments */
    .slider__item-content h1.slider__title {
        font-size: 1.5rem;
    }

    .slider__item-content p.slider__exerpt {
        font-size: 0.9rem;
    }

    /* Trending card adjustments */
    .trending-card__title {
        font-size: 0.9rem;
    }

    .trending-card__content p {
        font-size: 0.8rem;
    }

    /* Post card adjustments */
    .post-card__title-link {
        font-size: 1rem;
    }

    .post-card__exerpt {
        font-size: 0.8rem;
    }

    /* Images */
    .post-card__image img,
    .trending-card__img,
    .slider__item {
        height: auto;
        object-fit: cover;
    }
}


}
</style>

<!-- ================= JAVASCRIPT ================= -->
<script>
function toggleMinimalShareMenu(postId) {
    const menu = document.getElementById(`share-minimal-menu-${postId}`);
    menu.classList.toggle('show');
    
    // Close other open menus
    document.querySelectorAll('.share-minimal-menu.show').forEach(m => {
        if (m.id !== `share-minimal-menu-${postId}`) {
            m.classList.remove('show');
        }
    });
}

function copyMinimalLink(url, postId) {
    navigator.clipboard.writeText(url).then(() => {
        const successMsg = document.getElementById(`copy-minimal-success-${postId}`);
        successMsg.style.display = 'block';
        
        setTimeout(() => {
            successMsg.style.display = 'none';
        }, 2000);
    }).catch(() => {
        alert('Could not copy link');
    });
}

// Close share menus when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.share-minimal-dropdown')) {
        document.querySelectorAll('.share-minimal-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {

    // Thumbs slider
    const thumbsSwiper = new Swiper('.slider__bottom', {
        slidesPerView: 4,
        spaceBetween: 10,
        watchSlidesProgress: true,
        breakpoints: {
            0: { slidesPerView: 2 },
            576: { slidesPerView: 3 },
            768: { slidesPerView: 4 }
        }
    });

    // Main slider
    const mainSwiper = new Swiper('.slider__top', {
        loop: true,
        spaceBetween: 10,
        autoplay: {
            delay: 5000, // Slide every 5 seconds
            disableOnInteraction: false,
        },
        thumbs: {
            swiper: thumbsSwiper
        }
    });

});
</script>

@endsection