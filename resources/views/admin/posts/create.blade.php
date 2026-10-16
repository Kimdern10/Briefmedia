@extends('layouts.admin')

@section('title', 'New post')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">New post</h1>
        <p class="ad-head__sub">Write, add images, tune SEO and publish.</p>
    </div>
    <div class="ad-head__actions">
        <a href="{{ route('admin.posts.index') }}" class="ad-btn ad-btn--ghost"><i class="bi bi-arrow-left"></i> All posts</a>
    </div>
</div>

@include('admin.posts.form', ['categories' => $categories])
@endsection
