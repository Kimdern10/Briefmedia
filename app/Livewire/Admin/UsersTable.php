<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\AdminComponent;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin users list (mode "active", readers only) and deleted users (mode "trash"):
 * ban, unban, delete, restore and permanent delete without page reloads.
 * Mirrors the user actions in AdminController. Admin accounts are never listed or touched.
 */
class UsersTable extends Component
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

    private function reader(int $userId, bool $trashed = false): User
    {
        return ($trashed ? User::onlyTrashed() : User::query())
            ->where('role_as', 0)
            ->findOrFail($userId);
    }

    public function ban(int $userId): void
    {
        $user = $this->reader($userId);
        $user->active = 0;
        $user->save();

        $this->toast("{$user->name} has been banned.");
    }

    public function unban(int $userId): void
    {
        $user = $this->reader($userId);
        $user->active = 1;
        $user->save();

        $this->toast("{$user->name} has been unbanned.");
    }

    public function delete(int $userId): void
    {
        $user = $this->reader($userId);
        $user->delete();

        $this->toast("{$user->name} moved to deleted users.");
    }

    public function restore(int $userId): void
    {
        $user = $this->reader($userId, trashed: true);
        $user->restore();

        $this->toast("{$user->name} has been restored.");
    }

    public function forceDelete(int $userId): void
    {
        $user = $this->reader($userId, trashed: true);
        $name = $user->name;
        $user->forceDelete();

        $this->toast("{$name} has been permanently deleted.");
    }

    public function render(): View
    {
        $search = trim($this->search);

        $users = ($this->mode === 'trash' ? User::onlyTrashed() : User::query())
            ->where('role_as', 0)
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")->orWhere('email', 'LIKE', "%{$search}%");
            }))
            ->latest($this->mode === 'trash' ? 'deleted_at' : 'created_at')
            ->paginate(10);

        return view('livewire.admin.users-table', ['users' => $users]);
    }
}
