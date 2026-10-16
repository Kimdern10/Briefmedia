@extends('layouts.admin')

@section('title', 'Posts')

@section('content')
@php $placeholderImage = asset('assets/img/bm-placeholder.svg'); @endphp

<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Posts</h1>
        <p class="ad-head__sub">{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }} · publish, edit and organise your stories.</p>
    </div>
    <div class="ad-head__actions">
        <a href="{{ route('admin.posts.trash') }}" class="ad-btn ad-btn--ghost"><i class="bi bi-trash3"></i> Trash</a>
        <a href="{{ route('admin.posts.create') }}" class="ad-btn ad-btn--primary"><i class="bi bi-plus-lg"></i> New post</a>
    </div>
</div>

<div class="ad-card">
    <div class="ad-card__head">
        <div class="ad-toolbar" style="width: 100%;">
            <label class="ad-search">
                <i class="bi bi-search"></i>
                <input type="search" placeholder="Filter this page…" data-table-filter="#posts-table" aria-label="Filter posts">
            </label>
            <div class="ad-segment" role="group" aria-label="Status filter">
                <button type="button" class="is-active" data-status="all">All</button>
                <button type="button" data-status="published">Published</button>
                <button type="button" data-status="draft">Drafts</button>
            </div>
        </div>
    </div>

    <div class="ad-table-wrap">
        <table class="ad-table" id="posts-table">
            <thead>
                <tr>
                    <th>Post</th>
                    <th>Status</th>
                    <th class="ad-num ad-hide-sm">Views</th>
                    <th class="ad-hide-sm">Created</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr data-row data-status="{{ $post->trashed() ? 'trashed' : $post->status }}">
                        <td>
                            <div class="ad-post-cell">
                                <img class="ad-thumb" alt="" loading="lazy"
                                     src="{{ !empty($post->images) ? asset('storage/' . array_values($post->images)[0]) : $placeholderImage }}"
                                     onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                                <div>
                                    <div class="ad-post-cell__title">{{ $post->title }}</div>
                                    <div class="ad-post-cell__sub">{{ $post->category->name ?? 'Uncategorized' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($post->trashed())
                                <span class="ad-pill ad-pill--red">Trashed</span>
                            @elseif($post->status === 'published')
                                <span class="ad-pill ad-pill--green">Published</span>
                            @else
                                <span class="ad-pill ad-pill--gray">Draft</span>
                            @endif
                        </td>
                        <td class="ad-num ad-hide-sm">{{ number_format($post->views ?? 0) }}</td>
                        <td class="ad-hide-sm" style="white-space: nowrap;">{{ $post->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="ad-table__actions">
                                @if(!$post->trashed())
                                    <form action="{{ route('admin.posts.toggle-status', $post->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        @if($post->status === 'published')
                                            <button class="ad-btn ad-btn--ghost ad-btn--sm" title="Move back to drafts"><i class="bi bi-eye-slash"></i> <span class="ad-hide-sm">Unpublish</span></button>
                                        @else
                                            <button class="ad-btn ad-btn--success ad-btn--sm" title="Publish now"><i class="bi bi-send"></i> <span class="ad-hide-sm">Publish</span></button>
                                        @endif
                                    </form>

                                    @if($post->status === 'published')
                                        <a href="{{ route('posts.show', $post) }}" target="_blank" class="ad-btn ad-btn--ghost ad-btn--sm ad-btn--icon" title="View on site"><i class="bi bi-box-arrow-up-right"></i></a>
                                    @endif

                                    <a href="{{ route('admin.posts.edit', $post->id) }}" class="ad-btn ad-btn--ghost ad-btn--sm ad-btn--icon" title="Edit"><i class="bi bi-pencil"></i></a>

                                    <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST"
                                          data-confirm="“{{ Str::limit($post->title, 60) }}” will be moved to the trash. You can restore it later."
                                          data-confirm-title="Move post to trash?" data-confirm-button="Move to trash">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ad-btn ad-btn--danger ad-btn--sm ad-btn--icon" title="Move to trash"><i class="bi bi-trash3"></i></button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.posts.restore', $post->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="ad-btn ad-btn--success ad-btn--sm">Restore</button>
                                    </form>
                                    <form action="{{ route('admin.posts.forceDelete', $post->id) }}" method="POST"
                                          data-confirm="This permanently deletes the post. This cannot be undone." data-confirm-button="Delete forever">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ad-btn ad-btn--danger ad-btn--sm">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="ad-table-empty">
                            <i class="bi bi-journal-plus"></i>
                            No posts yet. <a href="{{ route('admin.posts.create') }}" style="color: var(--ad-accent); font-weight: 600;">Write your first post</a>
                        </td>
                    </tr>
                @endforelse
                <tr data-filter-empty hidden><td colspan="5" class="ad-table-empty"><i class="bi bi-funnel"></i>No posts match this filter on this page.</td></tr>
            </tbody>
        </table>
    </div>

    {{ $posts->links('snippets.site.pagination') }}
</div>
@endsection
