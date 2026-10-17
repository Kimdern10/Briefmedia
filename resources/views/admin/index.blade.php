@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
@php
    $monthNames = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $postsPerMonthLabels = collect($postsPerMonth->keys())->sort()->map(fn ($month) => $monthNames[(int) $month] ?? $month)->values();
    $postsPerMonthValues = collect($postsPerMonth->toArray())->sortKeys()->values();
    $maxTrendScore = max(1, (int) $trendingPosts->max('trend_score'));
    $placeholderImage = route('theme.asset', ['img', 'bm-placeholder.svg']);
    $publishedPosts = max(0, $totalPosts - $draftPosts);

    $stats = [
        ['label' => 'Total posts', 'value' => $totalPosts, 'icon' => 'bi-newspaper', 'tone' => 'tone-pink'],
        ['label' => 'Published', 'value' => $publishedPosts, 'icon' => 'bi-check2-circle', 'tone' => 'tone-green'],
        ['label' => 'Drafts', 'value' => $draftPosts, 'icon' => 'bi-pencil', 'tone' => 'tone-amber'],
        ['label' => 'Total views', 'value' => $views, 'icon' => 'bi-eye', 'tone' => 'tone-blue'],
        ['label' => 'Likes · Subscribers', 'value' => number_format($likes) . ' · ' . number_format($subscribers), 'icon' => 'bi-heart', 'tone' => 'tone-violet'],
    ];
@endphp

<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ Str::before(auth()->user()->name, ' ') }} 👋</h1>
        <p class="ad-head__sub">Here's what's happening across BriefMedia today.</p>
    </div>
    <div class="ad-head__actions">
        <a href="{{ route('admin.news.headlines') }}" class="ad-btn ad-btn--ghost"><i class="bi bi-broadcast"></i> Headlines</a>
        <livewire:admin.newsletter-sender />
    </div>
</div>

<!-- ================= STATS ================= -->
<div class="ad-grid ad-grid--stats">
    @foreach($stats as $stat)
        <div class="ad-card ad-stat">
            <div class="ad-stat__icon {{ $stat['tone'] }}"><i class="bi {{ $stat['icon'] }}"></i></div>
            <div class="ad-stat__label">{{ $stat['label'] }}</div>
            <div class="ad-stat__value">{{ is_numeric($stat['value']) ? number_format($stat['value']) : $stat['value'] }}</div>
        </div>
    @endforeach
</div>

<!-- ================= CHARTS ================= -->
<div class="ad-grid ad-grid--3" style="margin-top: 18px;">
    <div class="ad-card">
        <div class="ad-card__head"><h3 class="ad-card__title"><i class="bi bi-bar-chart"></i> Posts per month</h3></div>
        <div class="ad-card__body"><div class="ad-chart"><canvas id="postsChart"></canvas></div></div>
    </div>
    <div class="ad-card">
        <div class="ad-card__head"><h3 class="ad-card__title"><i class="bi bi-graph-up"></i> Reads, last 7 days</h3></div>
        <div class="ad-card__body"><div class="ad-chart"><canvas id="viewsChart"></canvas></div></div>
    </div>
    <div class="ad-card">
        <div class="ad-card__head"><h3 class="ad-card__title"><i class="bi bi-people"></i> Subscriber growth</h3></div>
        <div class="ad-card__body"><div class="ad-chart"><canvas id="subscriberChart"></canvas></div></div>
    </div>
</div>

<!-- ================= TABLES ================= -->
<div class="ad-grid ad-grid--main" style="margin-top: 18px;">
    <div class="ad-card">
        <div class="ad-card__head">
            <h3 class="ad-card__title"><i class="bi bi-clock-history"></i> Latest posts</h3>
            <a href="{{ route('admin.posts.index') }}" class="ad-btn ad-btn--ghost ad-btn--sm">View all <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Post</th>
                        <th>Status</th>
                        <th class="ad-hide-sm">Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestPosts as $post)
                        <tr>
                            <td>
                                <div class="ad-post-cell">
                                    <img class="ad-thumb" alt="" loading="lazy"
                                         src="{{ $post->featured_image_1 ? asset('storage/' . $post->featured_image_1) : $placeholderImage }}"
                                         onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                                    <div>
                                        <div class="ad-post-cell__title">{{ $post->title }}</div>
                                        <div class="ad-post-cell__sub">{{ $post->category->name ?? 'Uncategorized' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($post->status === 'published')
                                    <span class="ad-pill ad-pill--green">Published</span>
                                @else
                                    <span class="ad-pill ad-pill--gray">Draft</span>
                                @endif
                            </td>
                            <td class="ad-hide-sm">{{ $post->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="ad-table__actions">
                                    <a href="{{ route('admin.posts.edit', $post->id) }}" class="ad-btn ad-btn--ghost ad-btn--sm ad-btn--icon" title="Edit"><i class="bi bi-pencil"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="ad-table-empty"><i class="bi bi-journal"></i>No posts yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="ad-stack">
        <div class="ad-card">
            <div class="ad-card__head"><h3 class="ad-card__title"><i class="bi bi-fire"></i> Trending now</h3></div>
            <ul class="ad-list">
                @forelse($trendingPosts as $post)
                    <li>
                        <span class="ad-rank">{{ $loop->iteration }}</span>
                        <div style="flex: 1; min-width: 0;">
                            <div class="ad-post-cell__title" style="font-size: 13px;">{{ $post->title }}</div>
                            <div class="ad-bar"><span style="width: {{ round(($post->trend_score / $maxTrendScore) * 100) }}%;"></span></div>
                            <div class="ad-post-cell__sub">
                                <i class="bi bi-eye"></i> {{ number_format($post->views) }}
                                · <i class="bi bi-heart"></i> {{ $post->trend_likes }}
                                · <i class="bi bi-chat"></i> {{ $post->trend_comments }}
                            </div>
                        </div>
                        <strong style="font-variant-numeric: tabular-nums;" title="Trend score">{{ number_format($post->trend_score) }}</strong>
                    </li>
                @empty
                    <li class="ad-post-cell__sub">Nothing trending. Click 🔥 Trend on a post in <a href="{{ route('admin.posts.index') }}">Posts</a> to feature it for 2 days.</li>
                @endforelse
            </ul>
        </div>

        <div class="ad-card">
            <div class="ad-card__head"><h3 class="ad-card__title"><i class="bi bi-lightning"></i> Quick actions</h3></div>
            <div class="ad-card__body">
                <div class="ad-quick">
                    <a href="{{ route('admin.posts.create') }}"><i class="bi bi-pencil-square tone-pink"></i> Write a post</a>
                    <a href="{{ route('admin.categories.create') }}"><i class="bi bi-folder-plus tone-blue"></i> Add category</a>
                    <a href="{{ route('admin.comments') }}"><i class="bi bi-chat-square-text tone-green"></i> Moderate comments</a>
                    <a href="{{ route('admin.seo.edit') }}"><i class="bi bi-search-heart tone-violet"></i> SEO settings</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const styles = getComputedStyle(document.documentElement);
    const accent = styles.getPropertyValue('--ad-accent').trim() || '#e4407f';
    const muted = styles.getPropertyValue('--ad-muted').trim() || '#7a7a88';
    const line = styles.getPropertyValue('--ad-line').trim() || '#e7e7ec';

    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    Chart.defaults.color = muted;
    Chart.defaults.plugins.legend.display = false;

    const scales = {
        x: { grid: { display: false }, border: { display: false } },
        y: { beginAtZero: true, grid: { color: line }, border: { display: false }, ticks: { precision: 0 } }
    };

    const fill = (ctx) => {
        const { chart } = ctx;
        if (!chart.chartArea) { return 'transparent'; }
        const gradient = chart.ctx.createLinearGradient(0, chart.chartArea.top, 0, chart.chartArea.bottom);
        gradient.addColorStop(0, accent + '55');
        gradient.addColorStop(1, accent + '00');
        return gradient;
    };

    new Chart(document.getElementById('postsChart'), {
        type: 'bar',
        data: {
            labels: @json($postsPerMonthLabels),
            datasets: [{ label: 'Posts', data: @json($postsPerMonthValues), backgroundColor: accent, borderRadius: 6, maxBarThickness: 28 }]
        },
        options: { maintainAspectRatio: false, scales }
    });

    new Chart(document.getElementById('viewsChart'), {
        type: 'line',
        data: {
            labels: @json($viewsPerDay->pluck('date')),
            datasets: [{ label: 'Reads', data: @json($viewsPerDay->pluck('total')), borderColor: accent, backgroundColor: fill, fill: true, tension: .35, pointRadius: 3, pointBackgroundColor: accent }]
        },
        options: { maintainAspectRatio: false, scales }
    });

    new Chart(document.getElementById('subscriberChart'), {
        type: 'line',
        data: {
            labels: @json($subscriberGrowth->pluck('date')),
            datasets: [{ label: 'New subscribers', data: @json($subscriberGrowth->pluck('total')), borderColor: '#3b6ef5', backgroundColor: 'rgba(59,110,245,.12)', fill: true, tension: .35, pointRadius: 3, pointBackgroundColor: '#3b6ef5' }]
        },
        options: { maintainAspectRatio: false, scales }
    });
})();
</script>
@endpush
