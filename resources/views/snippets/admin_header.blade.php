<header class="ad-top">
    <button type="button" class="ad-btn ad-btn--ghost ad-btn--icon ad-burger" data-side-toggle aria-label="Toggle navigation">
        <i class="bi bi-list"></i>
    </button>

    <div>
        <div class="ad-top__title">@yield('title', 'Dashboard')</div>
        <div class="ad-top__crumb">{{ now()->format('l, F j') }}</div>
    </div>

    <div class="ad-top__actions">
        <a href="{{ route('admin.posts.create') }}" class="ad-btn ad-btn--primary ad-hide-sm"><i class="bi bi-plus-lg"></i> New post</a>

        <button type="button" class="ad-btn ad-btn--ghost ad-btn--icon ad-theme" data-theme-toggle aria-label="Toggle dark mode">
            <i class="bi bi-moon-stars"></i><i class="bi bi-sun"></i>
        </button>

        <div class="ad-dropdown">
            <button type="button" class="ad-avatar-btn" data-dropdown-toggle aria-label="Account menu">
                <span class="ad-initial">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                <span>{{ Str::before(auth()->user()->name ?? 'Admin', ' ') }}</span>
                <i class="bi bi-chevron-down" style="font-size: 11px;"></i>
            </button>
            <div class="ad-dropdown__menu">
                <div class="ad-dropdown__head">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->email }}</small>
                </div>
                <a href="{{ url('/') }}" target="_blank" class="ad-dropdown__item"><i class="bi bi-box-arrow-up-right"></i> View website</a>
                <a href="{{ route('profile') }}" class="ad-dropdown__item"><i class="bi bi-person"></i> My profile</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="ad-dropdown__item"><i class="bi bi-box-arrow-right"></i> Sign out</button>
                </form>
            </div>
        </div>
    </div>
</header>
