@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Categories</h1>
        <p class="ad-head__sub">Sections that organise your stories and power the site navigation.</p>
    </div>
    <div class="ad-head__actions">
        <a href="{{ route('admin.categories.trash') }}" class="ad-btn ad-btn--ghost"><i class="bi bi-trash3"></i> Trash</a>
        <a href="{{ route('admin.categories.create') }}" class="ad-btn ad-btn--primary"><i class="bi bi-plus-lg"></i> New category</a>
    </div>
</div>

<livewire:admin.categories-table mode="active" />
@endsection
