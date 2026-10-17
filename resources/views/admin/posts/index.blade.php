@extends('layouts.admin')

@section('title', 'Posts')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Posts</h1>
        <p class="ad-head__sub">Publish, edit and organise your stories.</p>
    </div>
    <div class="ad-head__actions">
        <a href="{{ route('admin.posts.trash') }}" class="ad-btn ad-btn--ghost"><i class="bi bi-trash3"></i> Trash</a>
        <a href="{{ route('admin.posts.create') }}" class="ad-btn ad-btn--primary"><i class="bi bi-plus-lg"></i> New post</a>
    </div>
</div>

<livewire:admin.posts-table mode="active" />
@endsection
