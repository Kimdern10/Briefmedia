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

<div class="ad-card">
    <div class="ad-table-wrap">
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="ad-hide-sm">Description</th>
                    <th class="ad-hide-sm">Deleted</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td class="ad-hide-sm" style="color: var(--ad-muted);">{{ $category->description ? Str::limit($category->description, 60) : 'No description' }}</td>
                        <td class="ad-hide-sm" style="white-space: nowrap;">{{ $category->deleted_at ? $category->deleted_at->format('j M Y, h:i A') : 'Unknown' }}</td>
                        <td>
                            <div class="ad-table__actions">
                                <form action="{{ route('admin.categories.restore', $category->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="ad-btn ad-btn--success ad-btn--sm"><i class="bi bi-arrow-counterclockwise"></i> Restore</button>
                                </form>
                                <form action="{{ route('admin.categories.forceDelete', $category->id) }}" method="POST"
                                      data-confirm="“{{ $category->name }}” will be deleted permanently. This cannot be undone."
                                      data-confirm-title="Delete forever?" data-confirm-button="Delete forever">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ad-btn ad-btn--danger ad-btn--sm"><i class="bi bi-x-octagon"></i> <span class="ad-hide-sm">Delete forever</span></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="ad-table-empty"><i class="bi bi-trash3"></i>Trash is empty</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($categories, 'links'))
        {{ $categories->links('snippets.site.pagination') }}
    @endif
</div>
@endsection
