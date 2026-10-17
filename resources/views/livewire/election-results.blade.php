<section class="bm-results" id="election-results" aria-labelledby="results-title-{{ $election->id }}"
         @unless($election->is_final) wire:poll.15s.visible @endunless>
    @php
        $areaCount = $election->areas->count();
        $leader = $standings->first();
        $candidateById = $election->candidates->keyBy('id');
        $visibleAreas = $showAllAreas ? $election->areas : $election->areas->take(8);
    @endphp

    <header class="bm-results__head">
        <div>
            <span class="bm-results__status {{ $election->is_final ? 'is-final' : '' }}">
                @if($election->is_final)
                    <i class="bi bi-flag-fill"></i> Final results
                @else
                    <span class="bm-live__dot"></span> Counting
                @endif
            </span>
            <h2 class="bm-results__title" id="results-title-{{ $election->id }}">{{ $election->title }}</h2>
        </div>
        <div class="bm-results__progress" aria-label="{{ $reportedCount }} of {{ $areaCount }} reported">
            <strong>{{ $reportedCount }}<small>/{{ $areaCount }}</small></strong>
            <span>{{ $election->areaLabelFor($areaCount) }} reported</span>
            <div class="bm-results__meter"><i style="width: {{ $areaCount ? round($reportedCount / $areaCount * 100) : 0 }}%;"></i></div>
        </div>
    </header>

    <ol class="bm-results__standings">
        @foreach($standings as $candidate)
            <li class="bm-results__row {{ $loop->first && $candidate->total > 0 ? 'is-leading' : '' }}" wire:key="std-{{ $candidate->id }}">
                <div class="bm-results__who">
                    <span class="bm-results__party" style="background: {{ $candidate->color }};">{{ $candidate->party ?: mb_substr($candidate->name, 0, 1) }}</span>
                    <span class="bm-results__name">
                        {{ $candidate->name }}
                        @if($loop->first && $candidate->total > 0)
                            <em>{{ $election->is_final ? 'Winner' : 'Leading' }}</em>
                        @endif
                    </span>
                    <span class="bm-results__votes"><strong>{{ number_format($candidate->total) }}</strong> {{ $candidate->percent }}%</span>
                </div>
                <div class="bm-results__bar"><i style="width: {{ $candidate->percent }}%; background: {{ $candidate->color }};"></i></div>
            </li>
        @endforeach
    </ol>

    <p class="bm-results__meta">
        {{ number_format($totalVotes) }} votes counted
        @if($leader && $standings->count() > 1 && $leader->total > 0)
            · Margin {{ number_format($leader->total - $standings->get(1)->total) }}
        @endif
        · Updated {{ $election->updated_at->diffForHumans() }}
    </p>

    @if($areaCount)
        <div class="bm-results__areas">
            <h3 class="bm-results__subtitle">Results by {{ $election->area_label }}</h3>
            <div class="bm-results__table-wrap">
                <table class="bm-results__table">
                    <thead>
                        <tr>
                            <th scope="col">{{ $election->area_label }}</th>
                            @foreach($election->candidates as $candidate)
                                <th scope="col"><i style="background: {{ $candidate->color }};"></i>{{ $candidate->shortLabel() }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($visibleAreas as $area)
                            @php
                                $areaVotes = $votesByArea[$area->id] ?? [];
                                $areaWinnerId = array_sum($areaVotes) > 0 ? array_search(max($areaVotes), $areaVotes) : null;
                            @endphp
                            <tr wire:key="area-{{ $area->id }}" class="{{ $area->reported ? '' : 'is-pending' }}">
                                <th scope="row">
                                    {{ $area->name }}
                                    <small>{{ $area->reported ? 'Reported' : (array_sum($areaVotes) > 0 ? 'Partial' : 'Awaiting') }}</small>
                                </th>
                                @foreach($election->candidates as $candidate)
                                    <td class="{{ $areaWinnerId === $candidate->id ? 'is-winner' : '' }}"
                                        @if($areaWinnerId === $candidate->id) style="--winner: {{ $candidate->color }};" @endif>
                                        {{ isset($areaVotes[$candidate->id]) && array_sum($areaVotes) > 0 ? number_format($areaVotes[$candidate->id]) : '–' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($areaCount > 8)
                <button type="button" class="bm-btn bm-btn--ghost bm-btn--sm" wire:click="toggleAreas" style="margin-top: 12px;">
                    {{ $showAllAreas ? 'Show fewer' : 'Show all '.$areaCount.' '.$election->areaLabelFor($areaCount) }}
                </button>
            @endif
        </div>
    @endif
</section>
