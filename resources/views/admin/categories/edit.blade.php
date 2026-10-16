@extends('layouts.admin')

@section('title', 'Edit category')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Edit category</h1>
        <p class="ad-head__sub">{{ $category->name }}</p>
    </div>
    <div class="ad-head__actions">
        <a href="{{ route('admin.categories.index') }}" class="ad-btn ad-btn--ghost"><i class="bi bi-arrow-left"></i> All categories</a>
    </div>
</div>

@include('admin.categories.form', ['category' => $category])
@endsection
