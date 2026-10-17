<div class="ad-card ad-election" id="election-results">
    <div class="ad-card__head">
        <h3 class="ad-card__title">
            <i class="bi bi-check2-square"></i> Election results
            @if($election)
                <span class="ad-badge {{ $election->is_final ? 'ad-badge--green' : 'ad-badge--red' }}" style="margin-left: 8px;">
                    {{ $election->is_final ? 'Final' : 'Counting' }}
                </span>
            @endif
        </h3>
        @if($election && ! $editingSetup)
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button type="button" class="ad-btn ad-btn--ghost ad-btn--sm" wire:click="startSetup"><i class="bi bi-gear"></i> Candidates &amp; areas</button>
                <button type="button" class="ad-btn {{ $election->is_final ? 'ad-btn--ghost' : 'ad-btn--accent' }} ad-btn--sm"
                        x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.toggleFinal())"
                        data-confirm-title="{{ $election->is_final ? 'Go back to counting?' : 'Mark as final results?' }}"
                        data-confirm-text="{{ $election->is_final ? 'Readers will see “Counting” again.' : 'Readers will see “Final results” and the board stops refreshing.' }}"
                        data-confirm-button="{{ $election->is_final ? 'Back to counting' : 'Mark final' }}" data-tone="question">
                    <i class="bi {{ $election->is_final ? 'bi-arrow-counterclockwise' : 'bi-flag' }}"></i> {{ $election->is_final ? 'Back to counting' : 'Mark final' }}
                </button>
            </div>
        @endif
    </div>

    <div class="ad-card__body">
        @if(! $election && ! $editingSetup)
            <p class="ad-post-cell__sub" style="margin: 0 0 12px;">
                Show a live results board in this story: totals for each candidate with % bars, how many LGAs have reported,
                and a table for every area. Readers see it update by itself as you type in results.
            </p>
            <button type="button" class="ad-btn ad-btn--primary ad-btn--sm" wire:click="startSetup"><i class="bi bi-plus-lg"></i> Add results board</button>
        @elseif($editingSetup)
            <form wire:submit="saveSetup" class="ad-election__setup">
                <div class="ad-grid ad-grid--2" style="gap: 0 16px;">
                    <div class="ad-field">
                        <label class="ad-label" for="el-title">Board title</label>
                        <input type="text" id="el-title" class="ad-input @error('title') is-invalid @enderror" wire:model="title" maxlength="160" placeholder="Anambra Governorship Election 2025">
                        @error('title') <span class="ad-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="el-area-label">Areas are called</label>
                        <input type="text" id="el-area-label" class="ad-input" wire:model="areaLabel" maxlength="40" placeholder="LGA, State, Ward, Polling unit">
                    </div>
                </div>
                <div class="ad-grid ad-grid--2" style="gap: 0 16px;">
                    <div class="ad-field">
                        <label class="ad-label" for="el-candidates">Candidates <small>one per line: Name | Party | #colour</small></label>
                        <textarea id="el-candidates" rows="7" class="ad-textarea ad-ads__code @error('candidatesText') is-invalid @enderror" wire:model="candidatesText"
                                  placeholder="Charles Soludo | APGA | #1f8a4c&#10;Nicholas Ukachukwu | APC | #2f5bd3&#10;George Moghalu | LP | #d9342b"></textarea>
                        <div class="ad-help"><span>Party and colour are optional. Colours are picked for you if left out.</span></div>
                        @error('candidatesText') <span class="ad-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="el-areas">Areas <small>one per line</small></label>
                        <textarea id="el-areas" rows="7" class="ad-textarea @error('areasText') is-invalid @enderror" wire:model="areasText"
                                  placeholder="Aguata&#10;Anambra East&#10;Anambra West&#10;Anaocha&#10;Awka North&#10;Awka South&#10;…"></textarea>
                        <div class="ad-help"><span>Changing names here removes that area's votes; unchanged names keep them.</span></div>
                        @error('areasText') <span class="ad-error">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div style="display: flex; gap: 8px; justify-content: space-between; flex-wrap: wrap;">
                    <div>
                        @if($election)
                            <button type="button" class="ad-btn ad-btn--danger ad-btn--sm"
                                    x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.deleteBoard())"
                                    data-confirm-title="Delete the results board?" data-confirm-text="All votes typed in will be removed." data-confirm-button="Delete" data-tone="warning">
                                <i class="bi bi-trash3"></i> Delete board
                            </button>
                        @endif
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="ad-btn ad-btn--ghost ad-btn--sm" wire:click="cancelSetup">Cancel</button>
                        <button type="submit" class="ad-btn ad-btn--primary ad-btn--sm" wire:loading.attr="disabled" wire:target="saveSetup">
                            <i class="bi bi-check2"></i> Save board
                        </button>
                    </div>
                </div>
            </form>
        @else
            @php $reportedCount = $election->areas->where('reported', true)->count(); @endphp

            <div class="ad-election__summary">
                <strong>{{ $reportedCount }} of {{ $election->areas->count() }} {{ $election->areaLabelFor($election->areas->count()) }} reported</strong>
                <div class="ad-election__totals">
                    @foreach($standings as $candidate)
                        <span><i style="background: {{ $candidate->color }};"></i>{{ $candidate->shortLabel() }} <b>{{ number_format($candidate->total) }}</b> · {{ $candidate->percent }}%</span>
                    @endforeach
                </div>
            </div>

            <p class="ad-post-cell__sub" style="margin: 0 0 10px;">Type each area's votes, tick <strong>Reported</strong> when it's complete, then click <strong>Save</strong> on that row.</p>

            <div class="ad-table-wrap">
                <table class="ad-table ad-election__table">
                    <thead>
                        <tr>
                            <th>{{ $election->area_label }}</th>
                            @foreach($election->candidates as $candidate)
                                <th><i class="ad-election__swatch" style="background: {{ $candidate->color }};"></i>{{ $candidate->shortLabel() }}</th>
                            @endforeach
                            <th>Reported</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($election->areas as $area)
                            <tr wire:key="area-{{ $area->id }}">
                                <td><strong>{{ $area->name }}</strong></td>
                                @foreach($election->candidates as $candidate)
                                    <td>
                                        <input type="number" min="0" step="1" inputmode="numeric" class="ad-input ad-election__vote @error('votes.'.$area->id.'.'.$candidate->id) is-invalid @enderror"
                                               wire:model="votes.{{ $area->id }}.{{ $candidate->id }}" aria-label="{{ $candidate->shortLabel() }} votes in {{ $area->name }}">
                                    </td>
                                @endforeach
                                <td>
                                    <label class="ad-check" style="align-items: center;">
                                        <input type="checkbox" wire:model="reported.{{ $area->id }}"> <span class="ad-hide-sm">Done</span>
                                    </label>
                                </td>
                                <td>
                                    <button type="button" class="ad-btn ad-btn--primary ad-btn--sm" wire:click="saveArea({{ $area->id }})"
                                            wire:loading.attr="disabled" wire:target="saveArea({{ $area->id }})">
                                        <span wire:loading.remove wire:target="saveArea({{ $area->id }})">Save</span>
                                        <span wire:loading wire:target="saveArea({{ $area->id }})"><span class="ad-spinner"></span></span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @error('votes.*') <span class="ad-error">{{ $message }}</span> @enderror
        @endif
    </div>
</div>
