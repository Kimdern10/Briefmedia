<div>
    <h3 class="bm-widget__title"><i class="bi bi-key"></i> Change password</h3>

    @if($savedMessage)
        <div class="bm-alert bm-alert--success" role="status">{{ $savedMessage }}</div>
    @endif

    <form wire:submit="save" novalidate>
        <div class="bm-form-grid">
            <div class="bm-field bm-field--full">
                <label class="bm-label" for="current_password">Current password</label>
                <input id="current_password" type="password" wire:model="current_password" autocomplete="current-password"
                       class="bm-input @error('current_password') is-invalid @enderror" placeholder="Enter current password">
                @error('current_password')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="bm-field">
                <label class="bm-label" for="new_password">New password</label>
                <input id="new_password" type="password" wire:model="new_password" autocomplete="new-password"
                       class="bm-input @error('new_password') is-invalid @enderror" placeholder="At least 6 characters">
                @error('new_password')
                    <span class="bm-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="bm-field">
                <label class="bm-label" for="new_password_confirmation">Confirm new password</label>
                <input id="new_password_confirmation" type="password" wire:model="new_password_confirmation" autocomplete="new-password"
                       class="bm-input" placeholder="Repeat new password">
            </div>
        </div>

        <div class="bm-form-actions">
            <button type="submit" class="bm-btn bm-btn--primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save"><i class="bi bi-shield-check"></i> Update password</span>
                <span wire:loading wire:target="save">Updating…</span>
            </button>
        </div>
    </form>
</div>
