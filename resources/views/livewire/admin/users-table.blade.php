<div class="ad-card">
    <div class="ad-card__head">
        <div class="ad-toolbar" style="width: 100%;">
            <label class="ad-search">
                <i class="bi bi-search"></i>
                <input type="search" wire:model.live.debounce.350ms="search" placeholder="Search by name or email…" aria-label="Search users">
            </label>
            <span class="ad-spinner" wire:loading aria-hidden="true"></span>
        </div>
    </div>

    <div class="ad-table-wrap" wire:loading.class="is-loading" wire:target="search,gotoPage,nextPage,previousPage">
        <table class="ad-table">
            <thead>
                <tr>
                    <th>User</th>
                    @if($mode === 'active')
                        <th>Status</th>
                        <th class="ad-hide-sm">Joined</th>
                    @else
                        <th class="ad-hide-sm">Deleted</th>
                    @endif
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr wire:key="user-row-{{ $user->id }}">
                        <td>
                            <div class="ad-person">
                                <span class="ad-initial">{{ strtoupper(substr($user->name ?? '?', 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $user->name ?? 'N/A' }}</strong>
                                    <div class="ad-post-cell__sub">{{ $user->email ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        @if($mode === 'active')
                            <td>
                                @if($user->active)
                                    <span class="ad-pill ad-pill--green">Active</span>
                                @else
                                    <span class="ad-pill ad-pill--red">Banned</span>
                                @endif
                            </td>
                            <td class="ad-hide-sm" style="white-space: nowrap;">{{ $user->created_at ? $user->created_at->format('j M Y') : 'N/A' }}</td>
                        @else
                            <td class="ad-hide-sm" style="white-space: nowrap;">{{ $user->deleted_at ? $user->deleted_at->format('j M Y, H:i') : 'N/A' }}</td>
                        @endif
                        <td>
                            <div class="ad-table__actions">
                                @if($mode === 'active')
                                    @if($user->active)
                                        <button type="button" class="ad-btn ad-btn--ghost ad-btn--sm"
                                                x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.ban({{ $user->id }}))"
                                            data-confirm-text="Ban {{ $user->name }}? They won't be able to log in until you unban them.">
                                            <i class="bi bi-slash-circle"></i> Ban
                                        </button>
                                    @else
                                        <button type="button" class="ad-btn ad-btn--success ad-btn--sm" wire:click="unban({{ $user->id }})">
                                            <i class="bi bi-check-circle"></i> Unban
                                        </button>
                                    @endif
                                    <button type="button" class="ad-btn ad-btn--danger ad-btn--sm ad-btn--icon" title="Delete user"
                                            x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.delete({{ $user->id }}))"
                                            data-confirm-text="Move {{ $user->name }} to deleted users? You can restore them later.">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                @else
                                    <button type="button" class="ad-btn ad-btn--success ad-btn--sm" wire:click="restore({{ $user->id }})">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                    </button>
                                    <button type="button" class="ad-btn ad-btn--danger ad-btn--sm"
                                            x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.forceDelete({{ $user->id }}))"
                                            data-confirm-text="Delete {{ $user->name }}'s account permanently? This cannot be undone.">
                                        <i class="bi bi-x-octagon"></i> <span class="ad-hide-sm">Delete forever</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="ad-table-empty">
                            @if($search !== '')
                                <i class="bi bi-funnel"></i>No users match “{{ $search }}”.
                            @elseif($mode === 'trash')
                                <i class="bi bi-person-check"></i>No deleted users
                            @else
                                <i class="bi bi-people"></i>No users yet
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>
