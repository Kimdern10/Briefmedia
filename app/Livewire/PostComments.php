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

        Comment::create([
            'post_id' => $this->post->id,
            'user_id' => auth()->id(),
            'content' => trim($this->content),
        ]);

        $this->reset('content');
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
            ->findOrFail($this->replyingTo);

        Comment::create([
            'post_id' => $this->post->id,
            'user_id' => auth()->id(),
            'content' => trim($this->replyContent),
            'parent_id' => $parent->id,
        ]);

        $this->reset('replyingTo', 'replyContent');
    }

    /**
     * @return Collection<int, Comment>
     */
    public function comments(): Collection
    {
        return Comment::where('post_id', $this->post->id)
            ->whereNull('parent_id')
            ->with(['user:id,name', 'replies.user:id,name'])
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
