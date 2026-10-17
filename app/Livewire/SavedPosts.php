<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * "Saved stories" on the reader's profile page.
 */
class SavedPosts extends Component
{
    use WithPagination;

    public function remove(int $postId): void
    {
        auth()->user()->bookmarks()->detach($postId);
    }

    #[On('bookmarks-changed')]
    public function refreshList(): void
    {
        // Re-render with the latest saved stories
    }

    public function render(): View
    {
        return view('livewire.saved-posts', [
            'posts' => auth()->user()->bookmarks()
                ->where('status', 'published')
                ->with('category')
                ->paginate(6, pageName: 'saved'),
        ]);
    }
}
