@extends('layouts.admin')

@section('title', 'Edit post')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Edit post</h1>
        <p class="ad-head__sub">{{ Str::limit($post->title, 90) }}</p>
    </div>
    <div class="ad-head__actions">
        <a href="{{ route('admin.posts.index') }}" class="ad-btn ad-btn--ghost"><i class="bi bi-arrow-left"></i> All posts</a>
    </div>
</div>

@include('admin.posts.form', ['post' => $post, 'categories' => $categories])

{{-- Outside the post form: it has its own form and saves straight away --}}
<div style="margin-top: 22px;">
    <livewire:admin.live-updates-manager :post="$post" />
</div>
@endsection
