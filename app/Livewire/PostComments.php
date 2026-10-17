<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Comments and replies under an article. Same rules as CategoryPostController::storeComment
 * (logged-in users only, up to 2000 characters), but new comments appear without a page reload.
 */
class PostComments extends Component
{
    public Post $post;

    public string $content = '';

    public ?int $replyingTo = null;

    public string $replyContent = '';

    /** Shown after posting when the comment is waiting for approval. */
    public ?string $notice = null;

    public function mount(Post $post): void
    {
        $this->post = $post;
    }

    public function postComment(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $this->validate(
            ['content' => 'required|string|max:2000'],
            ['content.required' => 'Write something before posting.'],
        );

        $comment = Comment::create([
            'post_id' => $this->post->id,
            'user_id' => auth()->id(),
            'content' => trim($this->content),
            'approved_at' => Comment::approvedAtFor(auth()->user()),
        ]);

        $this->reset('content');
        $this->notice = $comment->isApproved() ? null : 'Thanks! Your comment will appear for everyone once it is approved.';
        $this->dispatch('comment-posted');
    }

    public function startReply(int $commentId): void
    {
        $this->resetValidation();
        $this->replyingTo = $this->replyingTo === $commentId ? null : $commentId;
        $this->replyContent = '';
    }

    public function postReply(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $this->validate(
            ['replyContent' => 'required|string|max:2000'],
            ['replyContent.required' => 'Write a reply before posting.'],
        );

        // The parent must be a top-level comment on this same post.
        $parent = Comment::where('post_id', $this->post->id)
            ->whereNull('parent_id')
            ->where(fn ($query) => $query->whereNotNull('approved_at')->orWhere('user_id', auth()->id()))
            ->findOrFail($this->replyingTo);

        Comment::create([
            'post_id' => $this->post->id,
            'user_id' => auth()->id(),
            'content' => trim($this->replyContent),
            'parent_id' => $parent->id,
            'approved_at' => Comment::approvedAtFor(auth()->user()),
        ]);

        $this->reset('replyingTo', 'replyContent');
        $this->notice = (int) auth()->user()->role_as === 1 ? null : 'Thanks! Your reply will appear for everyone once it is approved.';
    }

    /**
     * @return Collection<int, Comment>
     */
    public function comments(): Collection
    {
        $viewerId = auth()->id();

        // Approved comments, plus the viewer's own comments that are still waiting for approval
        $visible = function ($query) use ($viewerId) {
            $query->where(function ($query) use ($viewerId) {
                $query->whereNotNull('approved_at')
                    ->when($viewerId, fn ($query) => $query->orWhere('user_id', $viewerId));
            });
        };

        return Comment::where('post_id', $this->post->id)
            ->whereNull('parent_id')
            ->where($visible)
            ->with(['user:id,name', 'allReplies' => fn ($query) => $query->where($visible)->with('user:id,name')])
            ->latest()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.post-comments', [
            'comments' => $this->comments(),
        ]);
    }
}
