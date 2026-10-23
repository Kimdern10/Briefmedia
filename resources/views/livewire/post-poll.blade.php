<section class="bm-poll" aria-labelledby="poll-{{ $poll->id }}">
    <span class="bm-poll__eyebrow"><i class="bi bi-bar-chart-fill"></i> Reader poll</span>
    <h3 class="bm-poll__question" id="poll-{{ $poll->id }}">{{ $poll->question }}</h3>

    @php
        $leadingVotes = $poll->options->max('votes');
        $leaderCount = $total > 0 ? $poll->options->where('votes', $leadingVotes)->count() : 0;
    @endphp

    {{-- One list: each answer shows its current share, and before voting the whole row is the choice --}}
    <form wire:submit="vote" class="bm-poll__form">
        <ul class="bm-poll__results">
            @foreach($poll->options as $option)
                @php
                    $percent = $total > 0 ? round($option->votes / $total * 100) : 0;
                    $isLeading = $total > 0 && $option->votes === $leadingVotes;
                @endphp
                <li class="bm-poll__result {{ $voted || ! $canVote ? '' : 'is-choice' }}">
                    <div class="bm-poll__bar {{ $percent > 0 ? 'is-filled' : '' }}" style="width: {{ $percent }}%;"></div>
                    @if($voted || ! $canVote)
                        <span class="bm-poll__label">{{ $option->label }}</span>
                    @else
                        <label class="bm-poll__label">
                            <input type="radio" wire:model="choice" name="poll-{{ $poll->id }}" value="{{ $option->id }}">
                            <span>{{ $option->label }}</span>
                        </label>
                    @endif
                    <span class="bm-poll__score">
                        @if($isLeading)
                            <span class="bm-poll__leader">{{ $leaderCount > 1 ? 'Tied' : 'Leading' }}</span>
                        @endif
                        <strong class="bm-poll__percent">{{ $percent }}%</strong>
                    </span>
                </li>
            @endforeach
        </ul>

        @error('choice') <p class="bm-poll__error">{{ $message }}</p> @enderror

        @if($voted)
            <p class="bm-poll__total">Thanks for voting · {{ number_format($total) }} {{ Str::plural('vote', $total) }}</p>
        @elseif(! $canVote)
            <p class="bm-poll__total">Admin and staff accounts can't vote · {{ number_format($total) }} {{ Str::plural('vote', $total) }}</p>
        @else
            <div class="bm-poll__actions">
                <button type="submit" class="bm-btn bm-btn--primary bm-btn--sm" wire:loading.attr="disabled" wire:target="vote">
                    <span wire:loading.remove wire:target="vote">Vote</span>
                    <span wire:loading wire:target="vote">Voting…</span>
                </button>
                <span class="bm-poll__total">
                    {{ $total > 0 ? number_format($total).' '.Str::plural('vote', $total).' so far' : 'No votes yet, be the first' }}
                </span>
            </div>
        @endif
    </form>
</section>
