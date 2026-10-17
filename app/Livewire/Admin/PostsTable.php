<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\AdminComponent;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin posts list (mode "active") and posts trash (mode "trash"): search, filter,
 * publish/unpublish, trash, restore and permanent delete without page reloads.
 * Mirrors the actions in Admin\PostController.
 */
class PostsTable extends Component
{
    use AdminComponent;
    use WithPagination;

    #[Locked]
    public string $mode = 'active';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $status = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setStatus(string $status): void
    {
        $this->status = in_array($status, ['draft', 'published', 'scheduled', 'trending'], true) ? $status : 'all';
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    public function toggleStatus(int $postId): void
    {
        $post = Post::findOrFail($postId);
        // Publishing a scheduled post now cancels its schedule
        $post->update([
            'status' => $post->status === 'published' ? 'draft' : 'published',
            'publish_at' => null,
        ]);

        // A draft can't stay pinned to Trending
        if ($post->status === 'draft' && $post->trending_until) {
            $post->forceFill(['trending_until' => null])->save();
        }

        Log::info('Post status toggled', ['post_id' => $post->id, 'new_status' => $post->status, 'admin_id' => Auth::id()]);
        $this->toast($post->status === 'published' ? 'Post published.' : 'Post moved back to drafts.');
    }

    /**
     * 🔥 Trend button: show a published post first in Trending for 2 days, or stop it early.
     */
    public function toggleTrending(int $postId): void
    {
        $post = Post::findOrFail($postId);

        if ($post->isPinnedTrending()) {
            $post->forceFill(['trending_until' => null])->save();
            $this->toast('Removed from Trending.');

            return;
        }

        if ($post->status !== 'published') {
            $this->toast('Publish the post first, then mark it as trending.', 'warning');

            return;
        }

        $post->forceFill(['trending_until' => now()->addDays(Post::PINNED_TRENDING_DAYS)])->save();

        Log::info('Post marked as trending', ['post_id' => $post->id, 'until' => $post->trending_until, 'admin_id' => Auth::id()]);
        $this->toast('🔥 Trending for the next '.Post::PINNED_TRENDING_DAYS.' days.');
    }

    public function trash(int $postId): void
    {
        $post = Post::findOrFail($postId);
        $post->forceFill(['trending_until' => null])->save();
        $post->delete();

        Log::info('Post moved to trash', ['post_id' => $post->id, 'title' => $post->title, 'admin_id' => Auth::id()]);
        $this->toast('Post moved to trash.');
    }

    public function restore(int $postId): void
    {
        $post = Post::onlyTrashed()->findOrFail($postId);
        $post->restore();

        Log::info('Post restored from trash', ['post_id' => $post->id, 'title' => $post->title, 'admin_id' => Auth::id()]);
        $this->toast('Post restored.');
    }

    public function forceDelete(int $postId): void
    {
        $post = Post::onlyTrashed()->findOrFail($postId);

        foreach (['featured_image_1', 'featured_image_2', 'featured_image_3', 'featured_image_4', 'meta_image', 'og_image', 'twitter_image'] as $field) {
            if ($post->{$field}) {
                Storage::disk('public')->delete($post->{$field});
            }
        }

        $post->forceDelete();

        Log::info('Post permanently deleted', ['post_id' => $post->id, 'title' => $post->title, 'admin_id' => Auth::id()]);
        $this->toast('Post permanently deleted.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $base = ($this->mode === 'trash' ? Post::onlyTrashed() : Post::query())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('excerpt', 'LIKE', "%{$search}%")
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'LIKE', "%{$search}%"));
                });
            });

        $statusCounts = [
            'all' => (clone $base)->count(),
            'published' => (clone $base)->where('status', 'published')->count(),
            'draft' => (clone $base)->where('status', 'draft')->whereNull('publish_at')->count(),
            'scheduled' => (clone $base)->where('status', 'draft')->whereNotNull('publish_at')->count(),
            'trending' => (clone $base)->where('trending_until', '>', now())->count(),
        ];

        $posts = $base->with(['category'])
            ->when($this->status === 'published', fn ($query) => $query->where('status', 'published'))
            ->when($this->status === 'draft', fn ($query) => $query->where('status', 'draft')->whereNull('publish_at'))
            ->when($this->status === 'scheduled', fn ($query) => $query->where('status', 'draft')->whereNotNull('publish_at')->reorder('publish_at'))
            ->when($this->status === 'trending', fn ($query) => $query->where('trending_until', '>', now())->orderByDesc('trending_until'))
            ->latest($this->mode === 'trash' ? 'deleted_at' : 'created_at')
            ->paginate(10);

        return view('livewire.admin.posts-table', [
            'posts' => $posts,
            'statusCounts' => $statusCounts,
            'placeholderImage' => route('theme.asset', ['img', 'bm-placeholder.svg']),
        ]);
    }
}
