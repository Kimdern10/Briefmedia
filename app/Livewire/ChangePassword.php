<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Change-password form for the public account page and the admin profile page.
 * Same rules as ProfileController::passwordUpdate, but it saves in place.
 */
class ChangePassword extends Component
{
    /** "site" for the public account page, "admin" for the admin panel page. */
    #[Locked]
    public string $variant = 'site';

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public ?string $savedMessage = null;

    public function save(): void
    {
        $this->savedMessage = null;

        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Please enter your current password',
            'new_password.required' => 'Please enter a new password',
            'new_password.min' => 'New password must be at least 6 characters',
            'new_password.confirmed' => 'New password and confirm password do not match',
        ]);

        $throttleKey = 'change-password:'.auth()->id();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->addError('current_password', 'Too many attempts. Please wait a minute and try again.');

            return;
        }

        $user = auth()->user();

        if (! Hash::check($this->current_password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('current_password', 'The current password is incorrect');

            return;
        }

        RateLimiter::clear($throttleKey);

        $user->password = Hash::make($this->new_password);
        $user->save();

        $this->reset('current_password', 'new_password', 'new_password_confirmation');
        $this->savedMessage = 'Password updated successfully.';
    }

    public function render(): View
    {
        return view($this->variant === 'admin' ? 'livewire.admin.change-password' : 'livewire.change-password');
    }
}
