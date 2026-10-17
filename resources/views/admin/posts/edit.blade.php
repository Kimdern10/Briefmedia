@extends('layouts.admin')

@section('title', 'Edit post')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Edit post</h1>
        <p class="ad-head__sub">{{ Str::limit($post->title, 90) }}</p>
    </div>
    <div class="ad-head__actions">
        <a href="#live-coverage" class="ad-btn ad-btn--ghost">
            <i class="bi bi-broadcast-pin"></i> Live coverage
            @if($post->is_live)<span class="ad-live__badge" style="margin-left: 4px;"><span class="ad-live__dot"></span> LIVE</span>@endif
        </a>
        <a href="#election-results" class="ad-btn ad-btn--ghost"><i class="bi bi-check2-square"></i> Election results</a>
        <a href="{{ route('admin.posts.index') }}" class="ad-btn ad-btn--ghost"><i class="bi bi-arrow-left"></i> All posts</a>
    </div>
</div>

@include('admin.posts.form', ['post' => $post, 'categories' => $categories])

{{-- Outside the post form: these have their own forms and save straight away --}}
<div class="ad-stack" style="margin-top: 22px;">
    <livewire:admin.election-results-manager :post="$post" />
    <livewire:admin.live-updates-manager :post="$post" />
</div>
@endsection
