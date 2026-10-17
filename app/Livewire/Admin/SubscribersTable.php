<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\AdminComponent;
use App\Models\Subscriber;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin newsletter subscribers: search and remove without page reloads
 * (mirrors AdminController::destroys).
 */
class SubscribersTable extends Component
{
    use AdminComponent;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $subscriberId): void
    {
        $subscriber = Subscriber::findOrFail($subscriberId);
        $email = $subscriber->email;
        $subscriber->delete();

        $this->toast("{$email} removed from the newsletter.");
    }

    public function render(): View
    {
        $search = trim($this->search);

        $subscribers = Subscriber::query()
            ->when($search !== '', fn ($query) => $query->where('email', 'LIKE', "%{$search}%"))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.subscribers-table', [
            'subscribers' => $subscribers,
            'activeCount' => Subscriber::where('is_active', true)->count(),
        ]);
    }
}
