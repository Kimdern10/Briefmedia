@extends('layouts.app')

@section('content')
<main class="main">
    <!--Banner-->
    <section class="banner mt-130">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="banner__content">
                        <small class="banner__meta">
                            <a href="{{ route('home') }}" class="banner__link">Home</a>
                            <i class="bi bi-caret-right-fill banner__icon"></i>{{ $category->name }}
                        </small>
                        <h3 class="banner__title">Category : <span class="banner__category-color">{{ $category->name }}</span></h3>
                        <p class="banner__subtitle">{{ $category->description ?? 'No description available.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Success Message -->
    @if(session('success'))
        <div class="container-fluid mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!-- Error Message -->
    @if(session('error'))
        <div class="container-fluid mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!--blog-grid-->
    <section class="blog-grid">
        <div class="container-fluid">
            <div class="row">
                <!-- Main Posts -->
                <div class="col-xl-9 mt-30 side-content">
                    <div class="theiaStickySidebar">
                        <div class="row">
                            @forelse ($posts as $post)
                                @php
                                    $postUrl = route('posts.show', $post);
                                    $postTitle = $post->title;
                                @endphp
                                <div class="col-lg-6 col-md-6">
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

                                            <!-- Post Meta -->
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
                                                   <i class="bi bi-chat-left-text-fill"></i> {{ $post->comments_count }}
                                                </li>
                                                <li class="post-card__meta-item" 
                                                    x-data="{ 
                                                        liked: {{ $post->likedByUsers->contains(auth()->id()) ? 'true' : 'false' }},
                                                        count: {{ $post->likedByUsers->count() }},
                                                        loading: false
                                                    }">
                                                    @auth
                                                        <button @click="
                                                            if(loading) return;
                                                            loading = true;
                                                            fetch('{{ route('posts.like', $post) }}', {
                                                                method: 'POST',
                                                                headers: {
                                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                    'Accept': 'application/json',
                                                                    'Content-Type': 'application/json'
                                                                }
                                                            })
                                                            .then(res => res.json())
                                                            .then(data => {
                                                                liked = data.liked;
                                                                count = data.likes_count;
                                                                loading = false;
                                                            })
                                                            .catch(error => {
                                                                console.error('Error:', error);
                                                                loading = false;
                                                                alert('Something went wrong. Please try again.');
                                                            });
                                                        " 
                                                        :disabled="loading"
                                                        :class="{ 'opacity-50': loading, 'cursor-not-allowed': loading }"
                                                        class="btn btn-link like-btn p-0 border-0 text-decoration-none">
                                                            <i :class="liked ? 'bi-hand-thumbs-up-fill text-primary' : 'bi-hand-thumbs-up'" class="bi"></i>
                                                            <span x-text="liked ? 'Unlike' : 'Like'"></span>
                                                            (<span x-text="count"></span>)
                                                        </button>
                                                    @else
                                                        <a href="{{ route('login') }}" class="text-muted text-decoration-none">
                                                            <i class="bi bi-hand-thumbs-up"></i> Like ({{ $post->likedByUsers()->count() }})
                                                        </a>
                                                    @endauth
                                                </li>
                                                
                                                {{-- MINIMAL SHARE BUTTON --}}
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
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        No posts available in this category.
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <!-- Pagination -->
                        <div class="row mt-4">
                            <div class="col-lg-12">
                                <div class="d-flex justify-content-center">
                                    {{ $posts->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-xl-3 max-width side-sidebar">
                    <div class="theiaStickySidebar">

                        <!-- Categories Widget -->
                        <div class="widget">
                            <h5 class="widget__title">Categories</h5>
                            <ul class="widget__categories">
                                @foreach ($allCategories as $cat)
                                    <li class="widget__categories-item">
                                        <a href="{{ route('category.page', $cat->slug) }}" class="category widget__categories-link">{{ $cat->name }}</a>
                                        <span class="ml-auto widget__categories-number">{{ $cat->posts_count }} Posts</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Latest Posts Widget -->
                       <div class="widget mt-4">
    <h5 class="widget__title">Latest Posts</h5>
    <ul class="widget__latest-posts">
        @php
            // Take only 5 latest posts
            $filteredLatest = $latestPosts->take(5);
        @endphp
        
        @forelse ($filteredLatest as $i => $post)
            <li class="widget__latest-posts__item">
                <div class="widget__latest-posts-image">
                    <a href="{{ route('posts.show', $post) }}" class="widget__latest-posts-link"> 
                        <img src="{{ asset('storage/' . ($post->featured_image_1 ?? 'default.jpg')) }}" alt="{{ $post->title }}" class="widget__latest-posts-img">
                    </a>
                </div>
                <div class="widget__latest-posts-count">{{ $i + 1 }}</div>
                <div class="widget__latest-posts__content">
                    <p class="widget__latest-posts-title">
                        <a href="{{ route('posts.show', $post) }}" class="widget__latest-posts-link">{{ $post->title }}</a>
                    </p>
                    <small class="widget__latest-posts-date">
                        <i class="bi bi-clock-fill widget__latest-posts-icon"></i>{{ $post->created_at->format('F d, Y') }}
                        
                        {{-- Optional: Show time ago for newer posts --}}
                        @if($post->created_at->diffInDays(now()) < 7)
                            <span class="badge bg-danger ms-2">New</span>
                        @endif
                    </small>
                </div>
            </li>
        @empty
            <li class="widget__latest-posts__item">
                <div class="widget__latest-posts__content">
                    <p class="widget__latest-posts-title text-muted">No latest posts available</p>
                </div>
            </li>
        @endforelse
        
        {{-- Optional: Show count if less than 5 posts are available --}}
        @if($filteredLatest->count() > 0 && $filteredLatest->count() < 5)
            <li class="widget__latest-posts__item">
                <small class="text-muted">
                    Showing {{ $filteredLatest->count() }} of 5 latest posts
                </small>
            </li>
        @endif
    </ul>
</div>

                        <!-- Trending Posts Widget -->
   <div class="widget mt-4">
    <h5 class="widget__title">Trending Posts</h5>
    <ul class="widget__latest-posts">
        @php
            // Filter posts with views > 0 OR likes > 0, sort by views, and take 5
            $filteredTrending = $trendingPosts
                ->filter(function($post) {
                    $hasViews = $post->views > 0;
                    $hasLikes = isset($post->likedByUsers) ? $post->likedByUsers->count() > 0 : false;
                    return $hasViews || $hasLikes;
                })
                ->sortByDesc(function($post) {
                    // Sort by views first (highest first)
                    return $post->views;
                })
                ->values() // Reset keys after sorting
                ->take(5); // Take only 5 posts
        @endphp
        
        @forelse ($filteredTrending as $i => $post)
            <li class="widget__latest-posts__item">
                <div class="widget__latest-posts-image">
                    <a href="{{ route('posts.show', $post) }}" class="widget__latest-posts-link"> 
                        <img src="{{ asset('storage/' . ($post->featured_image_1 ?? 'default.jpg')) }}" alt="{{ $post->title }}" class="widget__latest-posts-img">
                    </a>
                </div>
                <div class="widget__latest-posts-count">{{ $i + 1 }}</div>
                <div class="widget__latest-posts__content">
                    <p class="widget__latest-posts-title">
                        <a href="{{ route('posts.show', $post) }}" class="widget__latest-posts-link">{{ $post->title }}</a>
                    </p>
                    <small class="widget__latest-posts-date">
                        <i class="bi bi-eye-fill widget__latest-posts-icon"></i> {{ $post->views ?? 0 }} Views
                        @if(isset($post->likedByUsers) && $post->likedByUsers->count() > 0)
                            <span class="ms-2">
                                <i class="bi bi-hand-thumbs-up-fill"></i> {{ $post->likedByUsers->count() }}
                            </span>
                        @endif
                        
                        {{-- Optional: Add a hot indicator for posts with high engagement --}}
                        @if($post->views > 100 || (isset($post->likedByUsers) && $post->likedByUsers->count() > 10))
                            <span class="ms-2 text-danger">
                                <i class="bi bi-fire"></i> Hot
                            </span>
                        @endif
                    </small>
                </div>
            </li>
        @empty
            <li class="widget__latest-posts__item">
                <div class="widget__latest-posts__content">
                    <p class="widget__latest-posts-title text-muted">No trending posts available</p>
                </div>
            </li>
        @endforelse
        
        {{-- Optional: Show count if less than 5 posts are available --}}
        @if($filteredTrending->count() > 0 && $filteredTrending->count() < 5)
            <li class="widget__latest-posts__item">
                <small class="text-muted">
                    Showing {{ $filteredTrending->count() }} of 5 trending posts
                </small>
            </li>
        @endif
    </ul>
</div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- newsletter
    <section class="newslettre__section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6 col-md-10 col-sm-11 m-auto">
                    <div class="newslettre">
                        <div class="newslettre__info">
                            <h3 class="newslettre__title">Get The Best Blog Stories into Your inbox!</h3>
                            <p class="newslettre__desc">Sign up for free and be the first to get notified about new posts.</p>
                        </div>

                        <form action="" method="POST" class="newslettre__form">
                            @csrf
                            <input type="email" name="email" class="newslettre__form-input form-control" placeholder="Your email address" required>   
                            <button class="newslettre__form-submit" type="submit">Subscribe</button>  
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section> -->
</main>

<!-- Minimal Share Menu Styles -->
<style>
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
}
</style>

<!-- Minimal Share JavaScript -->
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
</script>
@endsection