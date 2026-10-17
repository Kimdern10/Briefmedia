<?php

namespace App\Livewire;

use App\Models\Userprofile;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Profile card on the account page: clicking the photo uploads a new one straight away,
 * without reloading the page. Also refreshes when the profile form is saved.
 */
class ProfilePhoto extends Component
{
    use WithFileUploads;

    /** "site" for the public account page, "admin" for the admin panel page. */
    #[Locked]
    public string $variant = 'site';

    /** @var TemporaryUploadedFile|null */
    public $photo = null;

    public ?string $savedMessage = null;

    public function updatedPhoto(): void
    {
        $this->savedMessage = null;

        $this->validate(
            ['photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'],
            [
                'photo.image' => 'File must be an image.',
                'photo.mimes' => 'Image must be jpeg, png, jpg, or gif.',
                'photo.max' => 'Image size cannot exceed 2MB.',
            ],
        );

        Userprofile::replacePhotoFor(auth()->user(), $this->photo);

        $this->reset('photo');
        $this->savedMessage = 'Photo updated.';
        $this->dispatch('profile-photo-updated', url: $this->photoUrl());
    }

    #[On('profile-updated')]
    public function refreshCard(): void
    {
        // Re-render with the latest name, phone and address.
    }

    public function photoUrl(): string
    {
        $user = auth()->user()->fresh('Userprofile');

        return optional($user->Userprofile)->photoUrl()
            ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=e4407f&color=fff&size=240';
    }

    public function render(): View
    {
        return view($this->variant === 'admin' ? 'livewire.admin.profile-photo' : 'livewire.profile-photo', [
            'user' => auth()->user()->fresh('Userprofile'),
            'currentPhotoUrl' => $this->photoUrl(),
        ]);
    }
}
