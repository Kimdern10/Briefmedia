@extends('layouts.admin')

@section('title', 'Trashed posts')

@section('content')
@php $placeholderImage = route('theme.asset', ['img', 'bm-placeholder.svg']); @endphp

<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Trash</h1>
        <p class="ad-head__sub">Deleted posts stay here until you restore them or delete them for good.</p>
    </div>
    <div class="ad-head__actions">
        <a href="{{ route('admin.posts.index') }}" class="ad-btn ad-btn--ghost"><i class="bi bi-arrow-left"></i> Back to posts</a>
    </div>
</div>

<div class="ad-card">
    <div class="ad-table-wrap">
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Post</th>
                    <th>Status</th>
                    <th class="ad-hide-sm">Deleted</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>
                            <div class="ad-post-cell">
                                <img class="ad-thumb" alt="" loading="lazy"
                                     src="{{ $post->featured_image_1 ? asset('storage/' . $post->featured_image_1) : $placeholderImage }}"
                                     onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                                <div>
                                    <div class="ad-post-cell__title">{{ $post->title }}</div>
                                    <div class="ad-post-cell__sub">{{ $post->category->name ?? 'Uncategorized' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($post->status === 'published')
                                <span class="ad-pill ad-pill--green">Published</span>
                            @else
                                <span class="ad-pill ad-pill--gray">Draft</span>
                            @endif
                        </td>
                        <td class="ad-hide-sm" style="white-space: nowrap;">{{ $post->deleted_at->format('j M Y, h:i A') }}</td>
                        <td>
                            <div class="ad-table__actions">
                                <form action="{{ route('admin.posts.restore', $post->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="ad-btn ad-btn--success ad-btn--sm"><i class="bi bi-arrow-counterclockwise"></i> Restore</button>
                                </form>
                                <form action="{{ route('admin.posts.forceDelete', $post->id) }}" method="POST"
                                      data-confirm="“{{ Str::limit($post->title, 60) }}” and its images will be deleted permanently. This cannot be undone."
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

    @if(method_exists($posts, 'links'))
        {{ $posts->links('snippets.site.pagination') }}
    @endif
</div>
@endsection
