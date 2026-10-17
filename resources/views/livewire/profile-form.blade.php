<div>
    <h3 class="bm-widget__title"><i class="bi bi-person-circle"></i> Profile information</h3>

    @if($savedMessage)
        <div class="bm-alert bm-alert--success" role="status">{{ $savedMessage }}</div>
    @endif

    <form wire:submit="save" novalidate>
        <div class="bm-form-grid">
            <div class="bm-field bm-field--full">
                <label class="bm-label" for="name">Full name</label>
                <input id="name" type="text" wire:model="name" class="bm-input @error('name') is-invalid @enderror" placeholder="Enter your full name">
                @error('name')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="bm-field">
                <label class="bm-label" for="email">Email address</label>
                <input id="email" type="email" value="{{ auth()->user()->email }}" class="bm-input" readonly>
            </div>

            <div class="bm-field">
                <label class="bm-label" for="phone">Phone number</label>
                <input id="phone" type="text" wire:model="phone" class="bm-input @error('phone') is-invalid @enderror" placeholder="e.g. 08012345678">
                @error('phone')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="bm-field bm-field--full">
                <label class="bm-label" for="address">Address</label>
                <input id="address" type="text" wire:model="address" class="bm-input @error('address') is-invalid @enderror" placeholder="Enter your address">
                @error('address')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="bm-field bm-field--full">
                <span class="bm-label">Profile picture</span>

                @if($photo && ! $errors->has('photo'))
                    <div class="bm-photo-preview">
                        <img src="{{ $photo->temporaryUrl() }}" alt="New profile photo preview">
                        <div>
                            <strong>New photo selected</strong>
                            <small>It will be saved when you click Save changes.</small>
                            <button type="button" class="bm-reply-toggle" wire:click="removeSelectedPhoto"><i class="bi bi-x-lg"></i> Remove</button>
                        </div>
                    </div>
                @else
                    <label class="bm-dropzone" for="file-input">
                        <i class="bi bi-cloud-arrow-up" wire:loading.remove wire:target="photo"></i>
                        <span class="bm-search__spinner" wire:loading wire:target="photo"></span>
                        <span>
                            <span wire:loading.remove wire:target="photo">Click to upload a new photo</span>
                            <span wire:loading wire:target="photo">Uploading…</span>
                            <small>PNG, JPG or GIF, up to 2MB</small>
                        </span>
                        <input type="file" id="file-input" wire:model="photo" accept="image/*">
                    </label>
                @endif
                @error('photo')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="bm-form-actions">
            <button type="submit" class="bm-btn bm-btn--primary" wire:loading.attr="disabled" wire:target="save,photo">
                <span wire:loading.remove wire:target="save"><i class="bi bi-check2"></i> Save changes</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
            <a href="{{ url('/') }}" class="bm-btn bm-btn--ghost">Cancel</a>
        </div>
    </form>
</div>
