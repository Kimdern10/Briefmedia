<?php

namespace App\Livewire\Admin\Concerns;

/**
 * Shared behaviour for admin Livewire components.
 *
 * Livewire actions are sent to /livewire/update, which does not pass through the
 * `isAdmin` route middleware, so every admin component re-checks on every request.
 */
trait AdminComponent
{
    public function bootAdminComponent(): void
    {
        abort_unless((int) optional(auth()->user())->role_as === 1, 403);
    }

    /**
     * Show a toast in the admin panel (see the admin-toast listener in layouts.admin).
     */
    protected function toast(string $message, string $tone = 'success'): void
    {
        $this->dispatch('admin-toast', message: $message, tone: $tone);
    }

    /**
     * Use the admin pagination view, whose links switch pages without reloading.
     */
    public function paginationView(): string
    {
        return 'livewire.admin.pagination';
    }
}
