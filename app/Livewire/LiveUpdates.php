<?php

namespace App\Livewire;

use App\Models\LiveUpdate;
use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Timestamped updates on a live coverage post. While the post is live the list
 * refreshes itself every 20 seconds, so readers never need to reload.
 */
class LiveUpdates extends Component
{
    #[Locked]
    public int $postId;

    /** How many updates are shown; "Show older updates" adds more. */
    public int $limit = 15;

    public function mount(Post $post): void
    {
        $this->postId = $post->id;
    }

    public function showMore(): void
    {
        $this->limit += 15;
    }

    public function render(): View
    {
        $post = Post::findOrFail($this->postId);

        return view('livewire.live-updates', [
            'isLive' => $post->isLive(),
            'updates' => LiveUpdate::where('post_id', $post->id)->latest()->latest('id')->take($this->limit)->get(),
            'total' => LiveUpdate::where('post_id', $post->id)->count(),
        ]);
    }
}
