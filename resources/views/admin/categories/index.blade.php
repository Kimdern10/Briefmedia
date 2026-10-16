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

<div class="ad-card">
    <div class="ad-card__head">
        <label class="ad-search">
            <i class="bi bi-search"></i>
            <input type="search" placeholder="Filter categories…" data-table-filter="#categories-table" aria-label="Filter categories">
        </label>
    </div>
    <div class="ad-table-wrap">
        <table class="ad-table" id="categories-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="ad-hide-sm">Description</th>
                    <th class="ad-hide-sm">Created</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr data-row>
                        <td>
                            <div class="ad-person">
                                <span class="ad-initial">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $category->name }}</strong>
                                    <div class="ad-post-cell__sub">/category/{{ $category->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="ad-hide-sm" style="color: var(--ad-muted); max-width: 420px;">{{ $category->description ? Str::limit($category->description, 80) : 'No description' }}</td>
                        <td class="ad-hide-sm" style="white-space: nowrap;">{{ $category->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="ad-table__actions">
                                <a href="{{ route('category.page', $category->slug) }}" target="_blank" class="ad-btn ad-btn--ghost ad-btn--sm ad-btn--icon" title="View on site"><i class="bi bi-box-arrow-up-right"></i></a>
                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="ad-btn ad-btn--ghost ad-btn--sm ad-btn--icon" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                      data-confirm="“{{ $category->name }}” will be moved to the trash." data-confirm-title="Move category to trash?" data-confirm-button="Move to trash">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ad-btn ad-btn--danger ad-btn--sm ad-btn--icon" title="Move to trash"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="ad-table-empty"><i class="bi bi-folder2-open"></i>No categories yet</td></tr>
                @endforelse
                <tr data-filter-empty hidden><td colspan="4" class="ad-table-empty"><i class="bi bi-funnel"></i>No categories match this filter.</td></tr>
            </tbody>
        </table>
    </div>

    {{ $categories->links('snippets.site.pagination') }}
</div>
@endsection
