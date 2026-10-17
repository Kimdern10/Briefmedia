<div class="ad-card">
    <div class="ad-card__head">
        <div class="ad-toolbar" style="width: 100%;">
            <label class="ad-search">
                <i class="bi bi-search"></i>
                <input type="search" wire:model.live.debounce.350ms="search" placeholder="Search by email…" aria-label="Search subscribers">
            </label>
            <span class="ad-spinner" wire:loading aria-hidden="true"></span>
            <span class="ad-post-cell__sub" style="margin-left: auto;">{{ number_format($activeCount) }} active</span>
        </div>
    </div>

    <div class="ad-table-wrap" wire:loading.class="is-loading" wire:target="search,gotoPage,nextPage,previousPage">
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Status</th>
                    <th class="ad-hide-sm">Subscribed</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscribers as $subscriber)
                    <tr wire:key="subscriber-row-{{ $subscriber->id }}">
                        <td>
                            <div class="ad-person">
                                <span class="ad-initial"><i class="bi bi-envelope"></i></span>
                                <strong data-email>{{ $subscriber->email }}</strong>
                            </div>
                        </td>
                        <td>
                            @if($subscriber->is_active)
                                <span class="ad-pill ad-pill--green">Active</span>
                            @else
                                <span class="ad-pill ad-pill--gray">Unsubscribed</span>
                            @endif
                        </td>
                        <td class="ad-hide-sm" style="white-space: nowrap;">{{ $subscriber->created_at->format('j M Y') }} <span class="ad-post-cell__sub">· {{ $subscriber->created_at->diffForHumans() }}</span></td>
                        <td>
                            <div class="ad-table__actions">
                                <button type="button" class="ad-btn ad-btn--danger ad-btn--sm ad-btn--icon" title="Remove subscriber"
                                        x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.delete({{ $subscriber->id }}))"
                                            data-confirm-text="Remove {{ $subscriber->email }} from the newsletter?">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="ad-table-empty">
                            @if($search !== '')
                                <i class="bi bi-funnel"></i>No subscribers match “{{ $search }}”.
                            @else
                                <i class="bi bi-envelope-open"></i>No subscribers yet
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $subscribers->links() }}
</div>
