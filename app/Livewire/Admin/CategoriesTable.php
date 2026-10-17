<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\AdminComponent;
use App\Models\Category;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin categories list (mode "active") and category trash (mode "trash") without page reloads.
 * Mirrors Admin\CategoryController's destroy, restore and forceDelete.
 */
class CategoriesTable extends Component
{
    use AdminComponent;
    use WithPagination;

    #[Locked]
    public string $mode = 'active';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function trash(int $categoryId): void
    {
        Category::findOrFail($categoryId)->delete();
        $this->toast('Category moved to trash.');
    }

    public function restore(int $categoryId): void
    {
        Category::onlyTrashed()->findOrFail($categoryId)->restore();
        $this->toast('Category restored.');
    }

    public function forceDelete(int $categoryId): void
    {
        Category::onlyTrashed()->findOrFail($categoryId)->forceDelete();
        $this->toast('Category permanently deleted.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $categories = ($this->mode === 'trash' ? Category::onlyTrashed() : Category::query())
            ->withCount('posts')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")->orWhere('description', 'LIKE', "%{$search}%");
            }))
            ->latest($this->mode === 'trash' ? 'deleted_at' : 'created_at')
            ->paginate(10);

        return view('livewire.admin.categories-table', ['categories' => $categories]);
    }
}
