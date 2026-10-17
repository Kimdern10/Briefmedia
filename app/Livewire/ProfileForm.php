<?php

namespace App\Livewire;

use App\Models\Userprofile;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * "Profile information" form on the account page. Same rules as ProfileController::update,
 * but it saves in place (with a photo preview) instead of reloading the page.
 */
class ProfileForm extends Component
{
    use WithFileUploads;

    /** "site" for the public account page, "admin" for the admin panel page. */
    #[Locked]
    public string $variant = 'site';

    public string $name = '';

    public string $phone = '';

    public string $address = '';

    /** @var TemporaryUploadedFile|null */
    public $photo = null;

    public ?string $savedMessage = null;

    public function mount(): void
    {
        $user = auth()->user()->load('Userprofile');

        $this->name = (string) $user->name;
        $this->phone = (string) optional($user->Userprofile)->phone;
        $this->address = (string) optional($user->Userprofile)->address;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:225',
            'phone' => [
                'nullable',
                'string',
                'max:15',
                function (string $attribute, $value, $fail) {
                    if ($value && strlen(preg_replace('/[^0-9]/', '', $value)) != 11) {
                        $fail('The phone number must be exactly 11 digits.');
                    }
                },
            ],
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'Please enter your full name',
            'phone.max' => 'Phone number cannot exceed 15 characters',
            'photo.image' => 'File must be an image',
            'photo.mimes' => 'Image must be jpeg, png, jpg, or gif',
            'photo.max' => 'Image size cannot exceed 2MB',
        ];
    }

    public function updatedPhoto(): void
    {
        $this->savedMessage = null;
        $this->validateOnly('photo');
    }

    public function removeSelectedPhoto(): void
    {
        $this->reset('photo');
        $this->resetValidation('photo');
    }

    public function save(): void
    {
        $this->savedMessage = null;
        $this->validate();

        $user = auth()->user();
        $user->update(['name' => trim($this->name)]);

        Userprofile::updateOrCreate(
            ['user_id' => $user->id],
            ['phone' => $this->phone ?: null, 'address' => $this->address ?: null],
        );

        if ($this->photo) {
            Userprofile::replacePhotoFor($user, $this->photo);
            $this->reset('photo');

            $this->dispatch('profile-photo-updated', url: optional($user->fresh('Userprofile')->Userprofile)->photoUrl());
        }

        $this->savedMessage = 'Profile updated successfully.';
        $this->dispatch('profile-updated');
    }

    public function render(): View
    {
        return view($this->variant === 'admin' ? 'livewire.admin.profile-form' : 'livewire.profile-form');
    }
}
