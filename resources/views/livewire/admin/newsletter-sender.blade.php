<div class="ad-newsletter-sender">
    <button type="button" class="ad-btn ad-btn--accent"
            x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.send())"
            data-confirm-title="Send the newsletter now?"
            data-confirm-text="It will email the {{ min(5, \App\Models\Post::where('status', 'published')->count()) }} latest stories and the trending stories to {{ number_format($activeCount) }} active {{ Str::plural('subscriber', $activeCount) }}.{{ $lastSentAt ? ' The last one went out '.$lastSentAt->diffForHumans().'.' : '' }}"
            data-confirm-button="Yes, send it"
            data-tone="question"
            wire:loading.attr="disabled" wire:target="send">
        <span wire:loading.remove wire:target="send"><i class="bi bi-send"></i> Send newsletter</span>
        <span wire:loading wire:target="send"><span class="ad-spinner" style="border-top-color: #fff;"></span> Sending…</span>
    </button>
    <span class="ad-post-cell__sub">
        @if($lastSentAt)
            Last sent {{ $lastSentAt->diffForHumans() }}{{ $lastSentCount ? ' to '.number_format($lastSentCount) : '' }}
        @else
            {{ number_format($activeCount) }} active {{ Str::plural('subscriber', $activeCount) }}
        @endif
    </span>
</div>
