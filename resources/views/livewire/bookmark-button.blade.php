<span>
    @if($variant === 'pill')
        <button type="button" wire:click="toggle" wire:loading.attr="disabled"
                class="bm-btn bm-btn--ghost bm-btn--sm {{ $saved ? 'is-saved' : '' }}"
                aria-pressed="{{ $saved ? 'true' : 'false' }}">
            <i class="bi {{ $saved ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i> {{ $saved ? 'Saved' : 'Save for later' }}
        </button>
    @else
        <button type="button" wire:click="toggle" wire:loading.attr="disabled"
                class="bm-icon-btn {{ $saved ? 'is-saved' : '' }}"
                aria-pressed="{{ $saved ? 'true' : 'false' }}"
                aria-label="{{ $saved ? 'Remove from saved stories' : 'Save for later' }}"
                title="{{ $saved ? 'Saved' : 'Save for later' }}">
            <i class="bi {{ $saved ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>
        </button>
    @endif
</span>
