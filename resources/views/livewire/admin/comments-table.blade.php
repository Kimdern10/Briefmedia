<div class="ad-card">
    <div class="ad-card__head">
        <div class="ad-toolbar" style="width: 100%;">
            <label class="ad-search">
                <i class="bi bi-search"></i>
                <input type="search" wire:model.live.debounce.350ms="search" placeholder="Search by comment, author or post…" aria-label="Search comments">
            </label>
            <span class="ad-spinner" wire:loading aria-hidden="true"></span>

            <nav class="ad-segment" aria-label="Comment filter" style="margin-left: auto;">
                @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'all' => 'All'] as $filterKey => $filterLabel)
                    <a href="#" wire:click.prevent="setFilter('{{ $filterKey }}')" class="{{ $filter === $filterKey ? 'is-active' : '' }}">
                        {{ $filterLabel }} <span class="ad-segment__count">{{ number_format($counts[$filterKey]) }}</span>
                    </a>
                @endforeach
            </nav>
            @if($filter === 'pending' && $counts['pending'] > 1)
                <button type="button" class="ad-btn ad-btn--success ad-btn--sm"
                        x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.approveAllPending())"
                        data-confirm-title="Approve all pending comments?"
                        data-confirm-text="{{ $counts['pending'] }} comments will become visible on the site."
                        data-confirm-button="Approve all" data-tone="question">
                    <i class="bi bi-check2-all"></i> Approve all
                </button>
            @endif
        </div>
    </div>

    <div class="ad-table-wrap" wire:loading.class="is-loading" wire:target="search,gotoPage,nextPage,previousPage">
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Author</th>
                    <th>Comment</th>
                    <th class="ad-hide-sm">On post</th>
                    <th class="ad-hide-sm">Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($comments as $comment)
                    <tr wire:key="comment-row-{{ $comment->id }}">
                        <td>
                            <div class="ad-person">
                                <span class="ad-initial">{{ strtoupper(substr($comment->user->name ?? 'G', 0, 1)) }}</span>
                                <strong style="white-space: nowrap;">{{ $comment->user->name ?? 'Guest' }}</strong>
                            </div>
                        </td>
                        <td style="min-width: 240px; max-width: 460px;">
                            <div class="ad-badges" style="margin: 0 0 4px;">
                                @if($comment->isApproved())
                                    <span class="ad-pill ad-pill--green">Approved</span>
                                @else
                                    <span class="ad-pill ad-pill--amber">Pending</span>
                                @endif
                                @if($comment->parent_id)
                                    <span class="ad-pill ad-pill--gray">Reply</span>
                                @endif
                            </div>
                            {{ Str::limit($comment->content, 180) }}
                        </td>
                        <td class="ad-hide-sm" style="max-width: 260px;">
                            @if($comment->post)
                                <a href="{{ route('posts.show', $comment->post) }}#comments" target="_blank" class="ad-post-cell__title" style="font-weight: 500;">{{ $comment->post->title }}</a>
                            @else
                                <span style="color: var(--ad-muted);">Post deleted</span>
                            @endif
                        </td>
                        <td class="ad-hide-sm" style="white-space: nowrap;">{{ $comment->created_at->format('j M Y') }}</td>
                        <td>
                            <div class="ad-table__actions">
                                @if($comment->isApproved())
                                    <button type="button" class="ad-btn ad-btn--ghost ad-btn--sm" wire:click="unapprove({{ $comment->id }})" title="Hide from the site">
                                        <i class="bi bi-eye-slash"></i> <span class="ad-hide-sm">Hide</span>
                                    </button>
                                @else
                                    <button type="button" class="ad-btn ad-btn--success ad-btn--sm" wire:click="approve({{ $comment->id }})" title="Show on the site">
                                        <i class="bi bi-check2"></i> Approve
                                    </button>
                                @endif
                                <button type="button" class="ad-btn ad-btn--danger ad-btn--sm ad-btn--icon" title="Delete comment"
                                        x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.delete({{ $comment->id }}))"
                                            data-confirm-text="Delete this comment? This cannot be undone.">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="ad-table-empty">
                            @if($search !== '')
                                <i class="bi bi-funnel"></i>No comments match “{{ $search }}”.
                            @else
                                <i class="bi bi-chat-square"></i>{{ $filter === 'pending' ? 'No comments are waiting for approval' : 'No comments yet' }}
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $comments->links() }}
</div>
