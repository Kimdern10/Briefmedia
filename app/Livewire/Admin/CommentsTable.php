<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\AdminComponent;
use App\Models\Comment;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin comments: approve new comments before they appear publicly, hide or delete them.
 * Tabs: Pending (waiting for approval), Approved, All.
 */
class CommentsTable extends Component
{
    use AdminComponent;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'pending')]
    public string $filter = 'pending';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['pending', 'approved', 'all'], true) ? $filter : 'pending';
        $this->resetPage();
    }

    public function approve(int $commentId): void
    {
        Comment::findOrFail($commentId)->forceFill(['approved_at' => now()])->save();
        $this->toast('Comment approved. It is now visible on the site.');
    }

    public function unapprove(int $commentId): void
    {
        Comment::findOrFail($commentId)->forceFill(['approved_at' => null])->save();
        $this->toast('Comment hidden from the site.', 'info');
    }

    public function approveAllPending(): void
    {
        $count = Comment::pending()->update(['approved_at' => now()]);
        $this->toast("{$count} comment(s) approved.");
    }

    public function delete(int $commentId): void
    {
        Comment::findOrFail($commentId)->delete();
        $this->toast('Comment deleted.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $comments = Comment::with(['user:id,name', 'post:id,title,slug'])
            ->when($this->filter === 'pending', fn ($query) => $query->pending())
            ->when($this->filter === 'approved', fn ($query) => $query->approved())
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('content', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('post', fn ($post) => $post->where('title', 'LIKE', "%{$search}%"));
            }))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.comments-table', [
            'comments' => $comments,
            'counts' => [
                'pending' => Comment::pending()->count(),
                'approved' => Comment::approved()->count(),
                'all' => Comment::count(),
            ],
        ]);
    }
}
