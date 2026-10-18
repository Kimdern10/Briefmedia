<div class="ad-card">
    <div class="ad-card__head">
        <div class="ad-toolbar" style="width: 100%;">
            <label class="ad-search">
                <i class="bi bi-search"></i>
                <input type="search" wire:model.live.debounce.350ms="search"
                       placeholder="Search all posts by title, excerpt or category…" aria-label="Search posts">
            </label>
            @if($search !== '')
                <button type="button" class="ad-btn ad-btn--ghost" wire:click="clearSearch">Clear</button>
            @endif
            <span class="ad-spinner" wire:loading aria-hidden="true"></span>

            @if($mode === 'active')
                <nav class="ad-segment" aria-label="Status filter" style="margin-left: auto;">
                    @foreach(['all' => 'All', 'published' => 'Published', 'draft' => 'Drafts', 'scheduled' => 'Scheduled', 'trending' => '🔥 Trending'] as $statusKey => $statusLabel)
                        <a href="#" wire:click.prevent="setStatus('{{ $statusKey }}')" class="{{ $status === $statusKey ? 'is-active' : '' }}"
                           @if($status === $statusKey) aria-current="page" @endif>
                            {{ $statusLabel }} <span class="ad-segment__count">{{ $statusCounts[$statusKey] }}</span>
                        </a>
                    @endforeach
                </nav>
            @endif
        </div>
    </div>

    <div class="ad-table-wrap" wire:loading.class="is-loading" wire:target="search,setStatus,gotoPage,nextPage,previousPage,clearSearch">
        <table class="ad-table ad-table--stack">
            <thead>
                <tr>
                    <th>Post</th>
                    <th>Status</th>
                    @if($mode === 'active')
                        <th class="ad-num ad-hide-sm">Views</th>
                        <th class="ad-hide-sm">Created</th>
                    @else
                        <th class="ad-hide-sm">Deleted</th>
                    @endif
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr wire:key="post-row-{{ $post->id }}">
                        <td>
                            <div class="ad-post-cell">
                                <img class="ad-thumb" alt="" loading="lazy"
                                     src="{{ $post->featured_image_1 ? asset('storage/' . $post->featured_image_1) : $placeholderImage }}"
                                     onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                                <div>
                                    <div class="ad-post-cell__title">{{ $post->title }}</div>
                                    <div class="ad-post-cell__sub">
                                        {{ $post->category->name ?? 'Uncategorized' }}
                                        @if($post->author_name) · by {{ $post->author_name }} @endif
                                    </div>
                                    @if($post->isBreaking() || $post->isHeadline() || $post->video_url)
                                        <div class="ad-badges">
                                            @if($post->isBreaking())<span class="ad-badge ad-badge--breaking">Breaking</span>@endif
                                            @if($post->isHeadline())<span class="ad-badge ad-badge--headline">Headline</span>@endif
                                            @if($post->video_url)<span class="ad-badge ad-badge--video"><i class="bi bi-play-fill"></i> Video</span>@endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($post->status === 'published')
                                <span class="ad-pill ad-pill--green">Published</span>
                            @elseif($post->isScheduled())
                                <span class="ad-pill ad-pill--amber">Scheduled</span>
                                <div class="ad-trend-left" style="color: var(--ad-amber);" title="{{ $post->publish_at->format('l j F Y, g:i A') }}">
                                    Goes live {{ $post->publish_at->format('j M, g:i A') }}
                                </div>
                            @else
                                <span class="ad-pill ad-pill--gray">Draft</span>
                            @endif
                            @if($mode === 'active' && $post->isPinnedTrending())
                                <div class="ad-trend-left" title="Trending until {{ $post->trending_until->format('j M, g:i A') }}">
                                    🔥 Trending · {{ $post->trending_until->diffForHumans(['parts' => 2, 'short' => true, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) }} left
                                </div>
                            @endif
                        </td>
                        @if($mode === 'active')
                            <td class="ad-num ad-hide-sm">{{ number_format($post->views ?? 0) }}</td>
                            <td class="ad-hide-sm" style="white-space: nowrap;">{{ $post->created_at->format('d M Y') }}</td>
                        @else
                            <td class="ad-hide-sm" style="white-space: nowrap;">{{ $post->deleted_at->format('j M Y, h:i A') }}</td>
                        @endif
                        <td>
                            <div class="ad-table__actions">
                                @if($mode === 'active')
                                    @if($post->status === 'published')
                                        <button type="button" class="ad-btn ad-btn--ghost ad-btn--sm" wire:click="toggleStatus({{ $post->id }})" title="Move back to drafts">
                                            <i class="bi bi-eye-slash"></i> <span class="ad-hide-sm">Unpublish</span>
                                        </button>
                                        <a href="{{ route('posts.show', $post) }}" target="_blank" class="ad-btn ad-btn--ghost ad-btn--sm ad-btn--icon" title="View on site"><i class="bi bi-box-arrow-up-right"></i></a>
                                    @else
                                        <button type="button" class="ad-btn ad-btn--success ad-btn--sm" wire:click="toggleStatus({{ $post->id }})" title="Publish now">
                                            <i class="bi bi-send"></i> <span class="ad-hide-sm">Publish</span>
                                        </button>
                                    @endif
                                    @if($post->status === 'published')
                                        @if($post->isPinnedTrending())
                                            <button type="button" class="ad-btn ad-btn--trend is-on ad-btn--sm" wire:click="toggleTrending({{ $post->id }})" title="Stop trending now">
                                                🔥 <span class="ad-hide-sm">Trending</span>
                                            </button>
                                        @else
                                            <button type="button" class="ad-btn ad-btn--trend ad-btn--sm" wire:click="toggleTrending({{ $post->id }})" title="Show first in Trending for 2 days">
                                                🔥 <span class="ad-hide-sm">Trend</span>
                                            </button>
                                        @endif
                                    @endif
                                    <a href="{{ route('admin.posts.edit', $post->id) }}" class="ad-btn ad-btn--ghost ad-btn--sm ad-btn--icon" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <button type="button" class="ad-btn ad-btn--danger ad-btn--sm ad-btn--icon" title="Move to trash"
                                            x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.trash({{ $post->id }}))"
                                            data-confirm-text="Move “{{ Str::limit($post->title, 60) }}” to the trash? You can restore it later.">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                @else
                                    <button type="button" class="ad-btn ad-btn--success ad-btn--sm" wire:click="restore({{ $post->id }})">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                    </button>
                                    <button type="button" class="ad-btn ad-btn--danger ad-btn--sm"
                                            x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.forceDelete({{ $post->id }}))"
                                            data-confirm-text="Delete “{{ Str::limit($post->title, 60) }}” and its images permanently? This cannot be undone.">
                                        <i class="bi bi-x-octagon"></i> <span class="ad-hide-sm">Delete forever</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="ad-table-empty">
                            @if($search !== '' || $status !== 'all')
                                <i class="bi bi-funnel"></i>
                                @if($status === 'trending')
                                    No posts are marked as trending. Click 🔥 Trend on a published post to feature it for 2 days.
                                @else
                                    No {{ $status === 'all' ? '' : ($status === 'draft' ? 'draft' : 'published') }} posts @if($search !== '') match “{{ $search }}”@endif.
                                @endif
                            @elseif($mode === 'trash')
                                <i class="bi bi-trash3"></i>Trash is empty
                            @else
                                <i class="bi bi-journal-plus"></i>
                                No posts yet. <a href="{{ route('admin.posts.create') }}" style="color: var(--ad-accent); font-weight: 600;">Write your first post</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $posts->links() }}
</div>
