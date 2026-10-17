@php
    $draftCount = \App\Models\Post::where('status', 'draft')->count();
    $pendingComments = \App\Models\Comment::whereNull('approved_at')->count();

    $adminNav = [
        'Overview' => [
            ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'bi-grid-1x2', 'label' => 'Dashboard'],
        ],
        'Content' => [
            ['route' => 'admin.posts.index', 'match' => ['admin.posts.index', 'admin.posts.edit', 'admin.posts.trash'], 'icon' => 'bi-newspaper', 'label' => 'Posts', 'badge' => $draftCount ? $draftCount . ' draft' . ($draftCount > 1 ? 's' : '') : null],
            ['route' => 'admin.posts.create', 'match' => 'admin.posts.create', 'icon' => 'bi-pencil-square', 'label' => 'New post'],
            ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'icon' => 'bi-folder2', 'label' => 'Categories'],
            ['route' => 'admin.news.headlines', 'match' => 'admin.news.headlines', 'icon' => 'bi-broadcast', 'label' => 'Headlines'],
        ],
        'Audience' => [
            ['route' => 'admin.comments', 'match' => 'admin.comments', 'icon' => 'bi-chat-square-text', 'label' => 'Comments', 'badge' => $pendingComments ? $pendingComments.' new' : null],
            ['route' => 'admin.subscribers', 'match' => 'admin.subscribers', 'icon' => 'bi-envelope-paper', 'label' => 'Subscribers'],
            ['route' => 'user.list', 'match' => ['user.list', 'users.trashed'], 'icon' => 'bi-people', 'label' => 'Users'],
        ],
        'Settings' => [
            ['route' => 'admin.profile', 'match' => 'admin.profile', 'icon' => 'bi-person-circle', 'label' => 'My profile'],
            ['route' => 'admin.seo.edit', 'match' => 'admin.seo.edit', 'icon' => 'bi-search-heart', 'label' => 'SEO settings'],
            ['route' => 'admin.ads', 'match' => 'admin.ads', 'icon' => 'bi-badge-ad', 'label' => 'Ads'],
        ],
    ];
@endphp

<aside class="ad-side" aria-label="Admin navigation">
    <a href="{{ route('admin.dashboard') }}" class="ad-side__brand">
        <span class="ad-side__mark">B</span>
        <span>Brief<b>Media</b></span>
        <small>Admin</small>
    </a>

    @foreach($adminNav as $section => $links)
        <div class="ad-side__label">{{ $section }}</div>
        @foreach($links as $link)
            <a href="{{ route($link['route']) }}" class="ad-side__link {{ request()->routeIs(...(array) $link['match']) ? 'is-active' : '' }}">
                <i class="bi {{ $link['icon'] }}"></i>
                <span>{{ $link['label'] }}</span>
                @if(!empty($link['badge']))
                    <span class="ad-side__badge">{{ $link['badge'] }}</span>
                @endif
            </a>
        @endforeach
    @endforeach

    <div class="ad-side__foot">
        <div class="ad-side__cta">
            <strong>Write something great</strong>
            <p>Draft, preview SEO and publish in one place.</p>
            <a href="{{ route('admin.posts.create') }}" class="ad-btn ad-btn--accent ad-btn--sm" style="width: 100%;"><i class="bi bi-plus-lg"></i> New post</a>
        </div>
        <a href="{{ url('/') }}" target="_blank" class="ad-side__link" style="margin-top: 10px;">
            <i class="bi bi-box-arrow-up-right"></i> <span>View website</span>
        </a>
    </div>
</aside>
