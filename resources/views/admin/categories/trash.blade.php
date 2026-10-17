@extends('layouts.admin')

@section('title', 'Trashed categories')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Category trash</h1>
        <p class="ad-head__sub">Restore a category or delete it permanently.</p>
    </div>
    <div class="ad-head__actions">
        <a href="{{ route('admin.categories.index') }}" class="ad-btn ad-btn--ghost"><i class="bi bi-arrow-left"></i> Back to categories</a>
    </div>
</div>

<livewire:admin.categories-table mode="trash" />
@endsection
