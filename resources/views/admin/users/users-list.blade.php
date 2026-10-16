@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Users</h1>
        <p class="ad-head__sub">Registered readers. Ban accounts that break the rules.</p>
    </div>
    <div class="ad-head__actions">
        <a href="{{ route('users.trashed') }}" class="ad-btn ad-btn--ghost"><i class="bi bi-person-x"></i> Deleted users</a>
    </div>
</div>

<div class="ad-card">
    <div class="ad-card__head">
        <label class="ad-search">
            <i class="bi bi-search"></i>
            <input type="search" placeholder="Filter by name or email…" data-table-filter="#users-table" aria-label="Filter users">
        </label>
    </div>
    <div class="ad-table-wrap">
        <table class="ad-table" id="users-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Status</th>
                    <th class="ad-hide-sm">Joined</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr data-row>
                        <td>
                            <div class="ad-person">
                                <span class="ad-initial">{{ strtoupper(substr($user->name ?? '?', 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $user->name ?? 'N/A' }}</strong>
                                    <div class="ad-post-cell__sub">{{ $user->email ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->active)
                                <span class="ad-pill ad-pill--green">Active</span>
                            @else
                                <span class="ad-pill ad-pill--red">Banned</span>
                            @endif
                        </td>
                        <td class="ad-hide-sm" style="white-space: nowrap;">{{ $user->created_at ? $user->created_at->format('j M Y') : 'N/A' }}</td>
                        <td>
                            <div class="ad-table__actions">
                                <form action="{{ $user->active ? route('user.ban', $user->id) : route('user.unban', $user->id) }}" method="POST"
                                      @if($user->active) data-confirm="{{ $user->name }} won't be able to log in until you unban them." data-confirm-title="Ban this user?" data-confirm-button="Ban user" @endif>
                                    @csrf
                                    @method('PATCH')
                                    @if($user->active)
                                        <button type="submit" class="ad-btn ad-btn--ghost ad-btn--sm"><i class="bi bi-slash-circle"></i> Ban</button>
                                    @else
                                        <button type="submit" class="ad-btn ad-btn--success ad-btn--sm"><i class="bi bi-check-circle"></i> Unban</button>
                                    @endif
                                </form>
                                <form action="{{ route('user.delete', $user->id) }}" method="POST"
                                      data-confirm="{{ $user->name }} will be moved to deleted users. You can restore them later." data-confirm-title="Delete this user?" data-confirm-button="Delete user">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ad-btn ad-btn--danger ad-btn--sm ad-btn--icon" title="Delete user"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="ad-table-empty"><i class="bi bi-people"></i>No users found</td></tr>
                @endforelse
                <tr data-filter-empty hidden><td colspan="4" class="ad-table-empty"><i class="bi bi-funnel"></i>No users match this filter.</td></tr>
            </tbody>
        </table>
    </div>

    {{ $users->links('snippets.site.pagination') }}
</div>
@endsection
