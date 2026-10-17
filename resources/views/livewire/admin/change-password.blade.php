<div class="ad-card">
    <div class="ad-card__head"><h3 class="ad-card__title"><i class="bi bi-key"></i> Change password</h3></div>
    <form wire:submit="save" novalidate>
        <div class="ad-card__body">
            @if($savedMessage)
                <div class="ad-notice ad-notice--success" role="status"><i class="bi bi-shield-check"></i> <span>{{ $savedMessage }}</span></div>
            @endif

            <div class="ad-field">
                <label class="ad-label" for="admin-current-password">Current password</label>
                <input id="admin-current-password" type="password" wire:model="current_password" autocomplete="current-password"
                       class="ad-input @error('current_password') is-invalid @enderror">
                @error('current_password') <span class="ad-error">{{ $message }}</span> @enderror
            </div>
            <div class="ad-grid ad-grid--2" style="gap: 0 16px;">
                <div class="ad-field">
                    <label class="ad-label" for="admin-new-password">New password</label>
                    <input id="admin-new-password" type="password" wire:model="new_password" autocomplete="new-password"
                           class="ad-input @error('new_password') is-invalid @enderror" placeholder="At least 6 characters">
                    @error('new_password') <span class="ad-error">{{ $message }}</span> @enderror
                </div>
                <div class="ad-field">
                    <label class="ad-label" for="admin-new-password-confirmation">Confirm new password</label>
                    <input id="admin-new-password-confirmation" type="password" wire:model="new_password_confirmation" autocomplete="new-password"
                           class="ad-input">
                </div>
            </div>
        </div>
        <div class="ad-card__head" style="border-top: 1px solid var(--ad-line); border-bottom: 0; justify-content: flex-end;">
            <button type="submit" class="ad-btn ad-btn--primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save"><i class="bi bi-shield-check"></i> Update password</span>
                <span wire:loading wire:target="save">Updating…</span>
            </button>
        </div>
    </form>
</div>
