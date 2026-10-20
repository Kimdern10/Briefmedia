<section class="bm-poll" aria-labelledby="poll-{{ $poll->id }}">
    <span class="bm-poll__eyebrow"><i class="bi bi-bar-chart-fill"></i> Reader poll</span>
    <h3 class="bm-poll__question" id="poll-{{ $poll->id }}">{{ $poll->question }}</h3>

    @php
        $leadingVotes = $poll->options->max('votes');
        $leaderCount = $total > 0 ? $poll->options->where('votes', $leadingVotes)->count() : 0;
    @endphp
    <p class="bm-poll__standings-title">Current standings</p>
    @if($total === 0)
        <p class="bm-poll__total">No votes yet — be the first to vote.</p>
    @endif
    <ul class="bm-poll__results">
        @foreach($poll->options as $option)
            @php $percent = $total > 0 ? round($option->votes / $total * 100) : 0; @endphp
            <li class="bm-poll__result">
                <div class="bm-poll__bar" style="width: {{ $percent }}%;"></div>
                <span class="bm-poll__label">{{ $option->label }}</span>
                @if($total > 0 && $option->votes === $leadingVotes)
                    <span class="bm-poll__leader">{{ $leaderCount > 1 ? 'Tied for lead' : 'Leading' }}</span>
                @endif
                <strong class="bm-poll__percent">{{ $percent }}%</strong>
            </li>
        @endforeach
    </ul>
    @if($voted)
        <p class="bm-poll__total">Thanks for voting · {{ number_format($total) }} {{ Str::plural('vote', $total) }}</p>
    @else
        <form wire:submit="vote" class="bm-poll__form">
            @foreach($poll->options as $option)
                <label class="bm-poll__option">
                    <input type="radio" wire:model="choice" name="poll-{{ $poll->id }}" value="{{ $option->id }}">
                    <span>{{ $option->label }}</span>
                </label>
            @endforeach
            @error('choice') <p class="bm-poll__error">{{ $message }}</p> @enderror
            <div class="bm-poll__actions">
                <button type="submit" class="bm-btn bm-btn--primary bm-btn--sm" wire:loading.attr="disabled" wire:target="vote">
                    <span wire:loading.remove wire:target="vote">Vote</span>
                    <span wire:loading wire:target="vote">Voting…</span>
                </button>
                <span class="bm-poll__total">{{ number_format($total) }} {{ Str::plural('vote', $total) }} so far</span>
            </div>
        </form>
    @endif
</section>
