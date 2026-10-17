@extends('layouts.admin')

@section('title', 'Trashed posts')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Trash</h1>
        <p class="ad-head__sub">Deleted posts stay here until you restore them or delete them for good.</p>
    </div>
    <div class="ad-head__actions">
        <a href="{{ route('admin.posts.index') }}" class="ad-btn ad-btn--ghost"><i class="bi bi-arrow-left"></i> Back to posts</a>
    </div>
</div>

<livewire:admin.posts-table mode="trash" />
@endsection
