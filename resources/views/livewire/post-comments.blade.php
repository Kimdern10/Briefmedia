<section class="bm-comments" id="comments">
    <h2 class="bm-comments__title">Comments <span>({{ $comments->whereNotNull('approved_at')->count() }})</span></h2>

    @if($notice)
        <div class="bm-alert bm-alert--success" role="status"><i class="bi bi-hourglass-split"></i> {{ $notice }}</div>
    @endif

    @auth
        <form class="bm-comment-form" wire:submit="postComment">
            <label for="comment-content" class="bm-visually-hidden">Your comment</label>
            <textarea id="comment-content" wire:model="content" placeholder="Share your thoughts…" maxlength="2000"></textarea>
            @error('content')
                <span class="bm-error">{{ $message }}</span>
            @enderror
            <div class="bm-comment-form__foot">
                <small>Commenting as <strong>{{ auth()->user()->name }}</strong></small>
                <button type="submit" class="bm-btn bm-btn--primary bm-btn--sm" wire:loading.attr="disabled" wire:target="postComment">
                    <span wire:loading.remove wire:target="postComment">Post comment</span>
                    <span wire:loading wire:target="postComment">Posting…</span>
                </button>
            </div>
        </form>
    @else
        <div class="bm-comment-form bm-login-prompt">
            <span>Join the conversation. Log in to leave a comment.</span>
            <a href="{{ route('login') }}" class="bm-btn bm-btn--primary bm-btn--sm">Log in to comment</a>
        </div>
    @endauth

    @forelse($comments as $comment)
        <div class="bm-comment" wire:key="comment-{{ $comment->id }}">
            <span class="bm-comment__avatar">{{ strtoupper(substr(optional($comment->user)->name ?? 'G', 0, 1)) }}</span>
            <div>
                <div class="bm-comment__head">
                    <span class="bm-comment__name">{{ optional($comment->user)->name ?? 'Guest' }}</span>
                    <span class="bm-comment__time">{{ $comment->created_at->diffForHumans() }}</span>
                    @unless($comment->isApproved())<span class="bm-pending">Awaiting approval</span>@endunless
                </div>
                <p class="bm-comment__text">{{ $comment->content }}</p>

                @auth
                    <button type="button" class="bm-reply-toggle" wire:click="startReply({{ $comment->id }})">
                        <i class="bi bi-reply"></i> {{ $replyingTo === $comment->id ? 'Cancel' : 'Reply' }}
                    </button>

                    @if($replyingTo === $comment->id)
                        <form class="bm-reply-form" wire:submit="postReply">
                            <textarea wire:model="replyContent" class="bm-textarea" placeholder="Write a reply…" maxlength="2000"
                                      x-init="$el.focus()"></textarea>
                            @error('replyContent')
                                <span class="bm-error">{{ $message }}</span>
                            @enderror
                            <div class="bm-comment-form__foot">
                                <span></span>
                                <button type="submit" class="bm-btn bm-btn--primary bm-btn--sm" wire:loading.attr="disabled" wire:target="postReply">
                                    <span wire:loading.remove wire:target="postReply">Reply</span>
                                    <span wire:loading wire:target="postReply">Posting…</span>
                                </button>
                            </div>
                        </form>
                    @endif
                @endauth

                @if($comment->allReplies->isNotEmpty())
                    <div class="bm-comment__replies">
                        @foreach($comment->allReplies as $reply)
                            <div class="bm-comment" wire:key="reply-{{ $reply->id }}">
                                <span class="bm-comment__avatar">{{ strtoupper(substr(optional($reply->user)->name ?? 'G', 0, 1)) }}</span>
                                <div>
                                    <div class="bm-comment__head">
                                        <span class="bm-comment__name">{{ optional($reply->user)->name ?? 'Guest' }}</span>
                                        <span class="bm-comment__time">{{ $reply->created_at->diffForHumans() }}</span>
                                        @unless($reply->isApproved())<span class="bm-pending">Awaiting approval</span>@endunless
                                    </div>
                                    <p class="bm-comment__text">{{ $reply->content }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @empty
        <p class="bm-meta" style="padding: 8px 0;">No comments yet. Be the first to share your thoughts.</p>
    @endforelse
</section>
