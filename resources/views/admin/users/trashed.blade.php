@extends('layouts.admin')

@section('title', 'Deleted users')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Deleted users</h1>
        <p class="ad-head__sub">Restore an account or remove it permanently.</p>
    </div>
    <div class="ad-head__actions">
        <a href="{{ route('user.list') }}" class="ad-btn ad-btn--ghost"><i class="bi bi-arrow-left"></i> Back to users</a>
    </div>
</div>

<div class="ad-card">
    <div class="ad-table-wrap">
        <table class="ad-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th class="ad-hide-sm">Deleted</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="ad-person">
                                <span class="ad-initial">{{ strtoupper(substr($user->name ?? '?', 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $user->name ?? 'N/A' }}</strong>
                                    <div class="ad-post-cell__sub">{{ $user->email ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="ad-hide-sm" style="white-space: nowrap;">{{ $user->deleted_at ? $user->deleted_at->format('j M Y, H:i') : 'N/A' }}</td>
                        <td>
                            <div class="ad-table__actions">
                                <form action="{{ route('user.restore', $user->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="ad-btn ad-btn--success ad-btn--sm"><i class="bi bi-arrow-counterclockwise"></i> Restore</button>
                                </form>
                                <form action="{{ route('user.forceDelete', $user->id) }}" method="POST"
                                      data-confirm="{{ $user->name }}'s account will be deleted permanently. This cannot be undone."
                                      data-confirm-title="Delete forever?" data-confirm-button="Delete forever">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ad-btn ad-btn--danger ad-btn--sm"><i class="bi bi-x-octagon"></i> <span class="ad-hide-sm">Delete forever</span></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="ad-table-empty"><i class="bi bi-person-check"></i>No deleted users</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links('snippets.site.pagination') }}
</div>
@endsection
