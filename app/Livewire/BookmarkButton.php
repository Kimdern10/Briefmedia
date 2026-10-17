<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * "Save for later" toggle on an article. Saved stories are listed on the reader's profile.
 */
class BookmarkButton extends Component
{
    #[Locked]
    public int $postId;

    /** Button style: "icon" (round rail button) or "pill" (share bar button). */
    #[Locked]
    public string $variant = 'icon';

    public bool $saved = false;

    public function mount(Post $post, string $variant = 'icon'): void
    {
        $this->postId = $post->id;
        $this->variant = $variant;
        $this->saved = auth()->check() && auth()->user()->bookmarks()->whereKey($post->id)->exists();
    }

    public function toggle(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $post = Post::where('status', 'published')->findOrFail($this->postId);
        $changes = auth()->user()->bookmarks()->toggle($post->id);

        $this->saved = ! empty($changes['attached']);
        $this->dispatch('bookmarks-changed');
        $this->js("window.bmToast && window.bmToast('".($this->saved ? 'Saved. Find it in your profile under Saved stories.' : 'Removed from saved stories.')."', 'success', 3000)");
    }

    /**
     * Keep the rail button and the share-bar button on the same page in step.
     */
    #[On('bookmarks-changed')]
    public function refreshSaved(): void
    {
        $this->saved = auth()->check() && auth()->user()->bookmarks()->whereKey($this->postId)->exists();
    }

    public function render(): View
    {
        return view('livewire.bookmark-button');
    }
}
