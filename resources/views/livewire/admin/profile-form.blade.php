<div class="ad-card">
    <div class="ad-card__head"><h3 class="ad-card__title"><i class="bi bi-person-circle"></i> Profile information</h3></div>
    <form wire:submit="save" novalidate>
        <div class="ad-card__body">
            @if($savedMessage)
                <div class="ad-notice ad-notice--success" role="status"><i class="bi bi-check-circle"></i> <span>{{ $savedMessage }}</span></div>
            @endif

            <div class="ad-grid ad-grid--2" style="gap: 0 16px;">
                <div class="ad-field" style="grid-column: 1 / -1;">
                    <label class="ad-label" for="admin-name">Full name</label>
                    <input id="admin-name" type="text" wire:model="name" class="ad-input @error('name') is-invalid @enderror">
                    @error('name') <span class="ad-error">{{ $message }}</span> @enderror
                </div>

                <div class="ad-field">
                    <label class="ad-label" for="admin-email">Email address</label>
                    <input id="admin-email" type="email" value="{{ auth()->user()->email }}" class="ad-input" readonly style="opacity: .7;">
                </div>

                <div class="ad-field">
                    <label class="ad-label" for="admin-phone">Phone number</label>
                    <input id="admin-phone" type="text" wire:model="phone" class="ad-input @error('phone') is-invalid @enderror" placeholder="e.g. 08012345678">
                    @error('phone') <span class="ad-error">{{ $message }}</span> @enderror
                </div>

                <div class="ad-field" style="grid-column: 1 / -1;">
                    <label class="ad-label" for="admin-address">Address</label>
                    <input id="admin-address" type="text" wire:model="address" class="ad-input @error('address') is-invalid @enderror">
                    @error('address') <span class="ad-error">{{ $message }}</span> @enderror
                </div>

                <div class="ad-field" style="grid-column: 1 / -1;">
                    <span class="ad-label">Profile picture</span>
                    @if($photo && ! $errors->has('photo'))
                        <div class="ad-photo-preview">
                            <img src="{{ $photo->temporaryUrl() }}" alt="New profile photo preview">
                            <div>
                                <strong>New photo selected</strong>
                                <small>It will be saved when you click Save changes.</small>
                                <button type="button" class="ad-btn ad-btn--ghost ad-btn--sm" style="margin-top: 6px;" wire:click="removeSelectedPhoto">Remove</button>
                            </div>
                        </div>
                    @else
                        <label class="ad-dropzone" for="admin-photo-input">
                            <i class="bi bi-cloud-arrow-up" wire:loading.remove wire:target="photo"></i>
                            <span class="ad-spinner" wire:loading wire:target="photo"></span>
                            <span>
                                <span wire:loading.remove wire:target="photo">Click to upload a new photo</span>
                                <span wire:loading wire:target="photo">Uploading…</span>
                                <small>PNG, JPG or GIF, up to 2MB</small>
                            </span>
                            <input type="file" id="admin-photo-input" wire:model="photo" accept="image/*">
                        </label>
                    @endif
                    @error('photo') <span class="ad-error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="ad-card__head" style="border-top: 1px solid var(--ad-line); border-bottom: 0; justify-content: flex-end;">
            <button type="submit" class="ad-btn ad-btn--primary" wire:loading.attr="disabled" wire:target="save,photo">
                <span wire:loading.remove wire:target="save"><i class="bi bi-check2"></i> Save changes</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>
