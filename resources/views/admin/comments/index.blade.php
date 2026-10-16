@extends('layouts.admin')

@section('title', 'Comments')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Comments</h1>
        <p class="ad-head__sub">Keep the conversation healthy. Remove spam or abuse.</p>
    </div>
</div>

<div class="ad-card">
    <div class="ad-card__head">
        <label class="ad-search">
            <i class="bi bi-search"></i>
            <input type="search" placeholder="Filter comments…" data-table-filter="#comments-table" aria-label="Filter comments">
        </label>
    </div>
    <div class="ad-table-wrap">
        <table class="ad-table" id="comments-table">
            <thead>
                <tr>
                    <th>Author</th>
                    <th>Comment</th>
                    <th class="ad-hide-sm">On post</th>
                    <th class="ad-hide-sm">Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($comments as $comment)
                    <tr data-row>
                        <td>
                            <div class="ad-person">
                                <span class="ad-initial">{{ strtoupper(substr($comment->user->name ?? 'G', 0, 1)) }}</span>
                                <strong style="white-space: nowrap;">{{ $comment->user->name ?? 'Guest' }}</strong>
                            </div>
                        </td>
                        <td style="min-width: 240px; max-width: 460px;">{{ Str::limit($comment->content, 180) }}</td>
                        <td class="ad-hide-sm" style="max-width: 260px;">
                            @if($comment->post)
                                <a href="{{ route('posts.show', $comment->post) }}#comments" target="_blank" class="ad-post-cell__title" style="font-weight: 500;">{{ $comment->post->title }}</a>
                            @else
                                <span style="color: var(--ad-muted);">N/A</span>
                            @endif
                        </td>
                        <td class="ad-hide-sm" style="white-space: nowrap;">{{ $comment->created_at->format('j M Y') }}</td>
                        <td>
                            <div class="ad-table__actions">
                                <form action="{{ route('admin.comments.delete', $comment->id) }}" method="POST"
                                      data-confirm="This comment will be deleted." data-confirm-title="Delete comment?" data-confirm-button="Delete">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ad-btn ad-btn--danger ad-btn--sm ad-btn--icon" title="Delete comment"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="ad-table-empty"><i class="bi bi-chat-square"></i>No comments yet</td></tr>
                @endforelse
                <tr data-filter-empty hidden><td colspan="5" class="ad-table-empty"><i class="bi bi-funnel"></i>No comments match this filter.</td></tr>
            </tbody>
        </table>
    </div>

    {{ $comments->links('snippets.site.pagination') }}
</div>
@endsection
