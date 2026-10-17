<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Search box in the site header overlay: shows matching published stories while you type.
 * Pressing Enter / Search still goes to the full results page (user.posts.search).
 */
class LiveSearch extends Component
{
    public string $query = '';

    public function mount(): void
    {
        $this->query = (string) request('query', '');
    }

    /**
     * @return Collection<int, Post>
     */
    public function results(): Collection
    {
        $term = trim($this->query);

        if (mb_strlen($term) < 2) {
            return collect();
        }

        return Post::query()
            ->with('category:id,name,slug')
            ->where('status', 'published')
            ->where(function ($query) use ($term) {
                $query->where('title', 'LIKE', "%{$term}%")
                    ->orWhere('excerpt', 'LIKE', "%{$term}%");
            })
            ->latest()
            ->take(6)
            ->get(['id', 'title', 'slug', 'category_id', 'featured_image_1', 'created_at']);
    }

    public function render(): View
    {
        return view('livewire.live-search', [
            'results' => $this->results(),
        ]);
    }
}
