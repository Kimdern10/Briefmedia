<section class="bm-live" id="live-updates" @if($isLive) wire:poll.20s.visible @endif>
    @if($isLive || $updates->isNotEmpty())
        <header class="bm-live__head">
            @if($isLive)
                <span class="bm-live__badge"><span class="bm-live__dot"></span> Live</span>
                <span class="bm-live__note">Updates appear here automatically</span>
            @else
                <span class="bm-live__badge bm-live__badge--ended">Coverage ended</span>
            @endif
        </header>

        @forelse($updates as $update)
            <article class="bm-live__item" wire:key="live-{{ $update->id }}">
                <time class="bm-live__time" datetime="{{ $update->created_at->toIso8601String() }}" title="{{ $update->created_at->format('M j, Y g:i A') }}">
                    {{ $update->created_at->format('g:i A') }}
                    <small>{{ $update->created_at->isToday() ? 'Today' : $update->created_at->format('M j') }}</small>
                </time>
                <div class="bm-live__body">
                    @if($update->title)
                        <h3 class="bm-live__title">{{ $update->title }}</h3>
                    @endif
                    <div class="bm-live__text">{!! nl2br(e($update->body)) !!}</div>
                </div>
            </article>
        @empty
            <p class="bm-meta" style="margin: 0;">The first update is coming soon.</p>
        @endforelse

        @if($total > $updates->count())
            <button type="button" class="bm-btn bm-btn--ghost bm-btn--sm" wire:click="showMore" style="margin-top: 12px;">
                Show older updates
            </button>
        @endif
    @endif
</section>
