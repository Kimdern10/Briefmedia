<div>
    @if($confirmation)
        <div class="bm-subscribe-done" role="status">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ $confirmation }}</span>
        </div>
    @else
        <form wire:submit="subscribe" class="bm-subscribe" novalidate>
            <input type="email" wire:model="email" placeholder="you@example.com" required aria-label="Email address"
                   autocomplete="email" @error('email') aria-invalid="true" @enderror>
            <button class="bm-btn bm-btn--accent" type="submit" wire:loading.attr="disabled" wire:target="subscribe">
                <span wire:loading.remove wire:target="subscribe">Subscribe <i class="bi bi-arrow-right"></i></span>
                <span wire:loading wire:target="subscribe">Subscribing…</span>
            </button>
        </form>
        @error('email')
            <p class="bm-subscribe-error" role="alert">{{ $message }}</p>
        @enderror
    @endif
</div>
