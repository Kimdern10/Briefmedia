<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\AdminComponent;
use App\Models\LiveUpdate;
use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Live coverage panel on the post edit page: switch coverage on or off and post
 * timestamped updates that readers see without reloading.
 */
class LiveUpdatesManager extends Component
{
    use AdminComponent;

    #[Locked]
    public int $postId;

    public bool $isLive = false;

    public string $title = '';

    public string $body = '';

    public function mount(Post $post): void
    {
        $this->postId = $post->id;
        $this->isLive = (bool) $post->is_live;
    }

    public function toggleLive(): void
    {
        $post = Post::withTrashed()->findOrFail($this->postId);
        $post->forceFill(['is_live' => ! $post->is_live])->saveQuietly();
        $this->isLive = (bool) $post->is_live;

        $this->toast($this->isLive ? 'Live coverage is on. Readers see a LIVE badge.' : 'Live coverage ended.');
    }

    public function addUpdate(): void
    {
        $this->validate([
            'title' => 'nullable|string|max:160',
            'body' => 'required|string|max:5000',
        ], ['body.required' => 'Write the update first.']);

        LiveUpdate::create([
            'post_id' => $this->postId,
            'admin_id' => auth()->id(),
            'title' => trim($this->title) ?: null,
            'body' => trim($this->body),
        ]);

        $this->reset('title', 'body');
        $this->toast('Update posted.');
    }

    public function deleteUpdate(int $updateId): void
    {
        LiveUpdate::where('post_id', $this->postId)->whereKey($updateId)->delete();
        $this->toast('Update deleted.');
    }

    public function render(): View
    {
        return view('livewire.admin.live-updates-manager', [
            'updates' => LiveUpdate::where('post_id', $this->postId)->latest()->latest('id')->take(30)->get(),
        ]);
    }
}
