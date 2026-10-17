{{-- $navCategories, $moreCategories and $tickerPosts come from the snippets.header composer in AppServiceProvider --}}
@php
    $profileImage = null;
    if (auth()->check()) {
        $profileImage = optional(auth()->user()->Userprofile)->profile_picture
            ? auth()->user()->Userprofile->photoUrl()
            : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=e4407f&color=fff&size=120';
    }
@endphp

<!-- ================= TOP BAR ================= -->
<div class="bm-topbar">
    <div class="bm-container bm-topbar__inner">
        <span class="bm-topbar__date">{{ now()->format('l, F j, Y') }}</span>

        @if($tickerPosts->isNotEmpty())
            <div class="bm-ticker" aria-label="Latest headlines">
                <span class="bm-ticker__label">Latest</span>
                <div class="bm-ticker__track">
                    <div class="bm-ticker__items">
                        {{-- Rendered twice so the marquee loops seamlessly --}}
                        @foreach([1, 2] as $copy)
                            @foreach($tickerPosts as $tickerPost)
                                <a href="{{ route('posts.show', $tickerPost) }}" @if($copy === 2) aria-hidden="true" tabindex="-1" @endif>{{ $tickerPost->title }}</a>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="bm-topbar__social">
            <a href="https://www.facebook.com/BriefMediablog" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
            <a href="#" aria-label="X"><i class="bi bi-twitter-x"></i></a>
        </div>
    </div>
</div>

<!-- ================= HEADER ================= -->
<header class="bm-header">
    <div class="bm-container bm-header__inner">

        <a href="{{ url('/') }}" class="bm-logo" aria-label="BriefMedia home">
            <span class="bm-logo__mark">B</span>
            <span class="bm-logo__text">Brief<b>Media</b></span>
        </a>

        <nav class="bm-nav" aria-label="Main">
            <a href="{{ url('/') }}" class="bm-nav__link {{ request()->is('/') ? 'is-active' : '' }}">Home</a>

            @foreach($navCategories as $navCategory)
                <a href="{{ route('category.page', $navCategory->slug) }}"
                   class="bm-nav__link {{ request()->is('category/' . $navCategory->slug) ? 'is-active' : '' }}">
                    {{ $navCategory->name }}
                </a>
            @endforeach

            <div class="bm-dropdown">
                <button type="button" class="bm-nav__link" data-dropdown-toggle aria-expanded="false" aria-haspopup="true">
                    More <i class="bi bi-chevron-down" style="font-size: 11px;"></i>
                </button>
                <div class="bm-dropdown__menu bm-dropdown__menu--left">
                    @if($moreCategories->isNotEmpty())
                        <div class="bm-dropdown__label">Categories</div>
                        @foreach($moreCategories as $moreCategory)
                            <a class="bm-dropdown__item" href="{{ route('category.page', $moreCategory->slug) }}">{{ $moreCategory->name }}</a>
                        @endforeach
                        <div class="bm-dropdown__divider"></div>
                    @endif
                    <a class="bm-dropdown__item" href="{{ route('contact') }}"><i class="bi bi-envelope"></i> Contact us</a>
                    <a class="bm-dropdown__item" href="{{ route('terms') }}"><i class="bi bi-file-text"></i> Terms &amp; Conditions</a>
                    <a class="bm-dropdown__item" href="{{ route('privacy') }}"><i class="bi bi-shield-lock"></i> Privacy Policy</a>
                </div>
            </div>
        </nav>

        <div class="bm-header__actions">
            <button type="button" class="bm-icon-btn" data-search-open aria-label="Search (Ctrl+K)">
                <i class="bi bi-search"></i>
            </button>

            <button type="button" class="bm-icon-btn bm-theme-toggle" data-theme-toggle aria-label="Toggle dark mode">
                <i class="bi bi-moon-stars"></i><i class="bi bi-sun"></i>
            </button>

            @auth
                <div class="bm-dropdown">
                    <button type="button" class="bm-avatar" data-dropdown-toggle aria-expanded="false" aria-label="Account menu"
                            style="background-image: url('{{ $profileImage }}'); background-size: cover; background-position: center;">
                    </button>
                    <div class="bm-dropdown__menu">
                        <div class="bm-dropdown__label">{{ auth()->user()->name }}</div>
                        @if(auth()->user()->role_as == 1)
                            <a class="bm-dropdown__item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Admin dashboard</a>
                        @endif
                        <a class="bm-dropdown__item" href="{{ route('profile') }}"><i class="bi bi-person"></i> My profile</a>
                        <div class="bm-dropdown__divider"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="bm-dropdown__item"><i class="bi bi-box-arrow-right"></i> Log out</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="bm-btn bm-btn--ghost bm-btn--sm bm-hide-sm">Log in</a>
                <a href="{{ route('sign-up') }}" class="bm-btn bm-btn--primary bm-btn--sm bm-hide-sm">Sign up</a>
            @endauth

            <button type="button" class="bm-icon-btn bm-burger" data-drawer-open aria-label="Open menu">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>
</header>

<!-- ================= MOBILE DRAWER ================= -->
<div class="bm-drawer" id="bm-drawer" aria-hidden="true">
    <div class="bm-drawer__backdrop" data-drawer-close></div>
    <aside class="bm-drawer__panel" aria-label="Menu">
        <div class="bm-drawer__head">
            <a href="{{ url('/') }}" class="bm-logo">
                <span class="bm-logo__mark">B</span>
                <span class="bm-logo__text">Brief<b>Media</b></span>
            </a>
            <button type="button" class="bm-icon-btn" data-drawer-close aria-label="Close menu"><i class="bi bi-x-lg"></i></button>
        </div>

        <button type="button" class="bm-btn bm-btn--ghost bm-btn--block" data-search-open>
            <i class="bi bi-search"></i> Search stories
        </button>

        <div class="bm-drawer__section">Sections</div>
        <nav class="bm-drawer__links">
            <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'is-active' : '' }}">Home <i class="bi bi-arrow-right"></i></a>
            @foreach($navCategories->concat($moreCategories) as $drawerCategory)
                <a href="{{ route('category.page', $drawerCategory->slug) }}" class="{{ request()->is('category/' . $drawerCategory->slug) ? 'is-active' : '' }}">
                    {{ $drawerCategory->name }} <i class="bi bi-arrow-right"></i>
                </a>
            @endforeach
            <a href="{{ route('contact') }}">Contact us <i class="bi bi-arrow-right"></i></a>
        </nav>

        <div class="bm-drawer__section">Account</div>
        <nav class="bm-drawer__links">
            @auth
                @if(auth()->user()->role_as == 1)
                    <a href="{{ route('admin.dashboard') }}">Admin dashboard <i class="bi bi-speedometer2"></i></a>
                @endif
                <a href="{{ route('profile') }}">My profile <i class="bi bi-person"></i></a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bm-btn bm-btn--ghost bm-btn--block" style="margin-top: 8px;">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}">Log in <i class="bi bi-box-arrow-in-right"></i></a>
                <a href="{{ route('sign-up') }}">Create account <i class="bi bi-person-plus"></i></a>
            @endauth
        </nav>
    </aside>
</div>

<!-- ================= SEARCH OVERLAY ================= -->
<div class="bm-search" id="bm-search" aria-hidden="true" role="dialog" aria-label="Search">
    <div class="bm-search__box">
        <form action="{{ route('user.posts.search') }}" method="GET" class="bm-search__form">
            <i class="bi bi-search"></i>
            <input type="search" name="query" class="bm-search__input" placeholder="Search stories, topics, people…"
                   value="{{ request('query') }}" required autocomplete="off" aria-label="Search posts">
            <button type="submit" class="bm-btn bm-btn--primary bm-btn--sm">Search</button>
        </form>
        <div class="bm-search__hint">Press <kbd>Enter</kbd> to search · <kbd>Esc</kbd> to close · <kbd>Ctrl</kbd> + <kbd>K</kbd> anywhere</div>
    </div>
</div>
