<div class="ad-card ad-live" id="live-coverage">
    <div class="ad-card__head">
        <h3 class="ad-card__title">
            <i class="bi bi-broadcast-pin"></i> Live coverage
            @if($isLive)<span class="ad-live__badge"><span class="ad-live__dot"></span> LIVE</span>@endif
        </h3>
        <button type="button" class="ad-btn {{ $isLive ? 'ad-btn--ghost' : 'ad-btn--accent' }} ad-btn--sm"
                @if($isLive)
                    x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.toggleLive())"
                    data-confirm-title="End live coverage?" data-confirm-text="The LIVE badge goes away. Updates stay on the story." data-confirm-button="End coverage"
                @else
                    wire:click="toggleLive"
                @endif
                wire:loading.attr="disabled" wire:target="toggleLive">
            <i class="bi {{ $isLive ? 'bi-stop-circle' : 'bi-broadcast' }}"></i> {{ $isLive ? 'End coverage' : 'Go live' }}
        </button>
    </div>
    <div class="ad-card__body">
        <p class="ad-post-cell__sub" style="margin: 0 0 12px;">
            For elections, matches or budget day: post short updates here and they appear at the top of the story for readers, without them reloading.
        </p>

        <form wire:submit="addUpdate" class="ad-live__form">
            <input type="text" class="ad-input" wire:model="title" maxlength="160" placeholder="Short heading (optional), e.g. Results from Awka South">
            <textarea class="ad-textarea @error('body') is-invalid @enderror" wire:model="body" rows="3" maxlength="5000" placeholder="What just happened?"></textarea>
            @error('body') <span class="ad-error">{{ $message }}</span> @enderror
            @error('title') <span class="ad-error">{{ $message }}</span> @enderror
            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="ad-btn ad-btn--primary ad-btn--sm" wire:loading.attr="disabled" wire:target="addUpdate">
                    <span wire:loading.remove wire:target="addUpdate"><i class="bi bi-send"></i> Post update</span>
                    <span wire:loading wire:target="addUpdate"><span class="ad-spinner"></span> Posting…</span>
                </button>
            </div>
        </form>

        @if($updates->isNotEmpty())
            <ul class="ad-live__list">
                @foreach($updates as $update)
                    <li wire:key="lu-{{ $update->id }}">
                        <time>{{ $update->created_at->format('M j, g:i A') }}</time>
                        <div style="min-width: 0;">
                            @if($update->title)<strong>{{ $update->title }}</strong>@endif
                            <p>{{ Str::limit($update->body, 220) }}</p>
                        </div>
                        <button type="button" class="ad-btn ad-btn--danger ad-btn--sm ad-btn--icon" aria-label="Delete update"
                                x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.deleteUpdate({{ $update->id }}))"
                                data-confirm-title="Delete this update?" data-confirm-button="Delete" data-tone="warning">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
