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

<livewire:admin.users-table mode="trash" />
@endsection
