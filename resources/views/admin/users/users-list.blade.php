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

<livewire:admin.users-table mode="active" />
@endsection
