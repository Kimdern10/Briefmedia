<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\AdminComponent;
use App\Models\Election;
use App\Models\ElectionArea;
use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Election results panel on the post edit page: set up candidates and areas once,
 * then type in each area's votes as they come. Readers see the board update by itself.
 */
class ElectionResultsManager extends Component
{
    use AdminComponent;

    #[Locked]
    public int $postId;

    public bool $editingSetup = false;

    public string $title = '';

    public string $areaLabel = 'LGA';

    public string $candidatesText = '';

    public string $areasText = '';

    /** @var array<int, array<int, int|string|null>> area id => candidate id => votes */
    public array $votes = [];

    /** @var array<int, bool> area id => finished reporting */
    public array $reported = [];

    public function mount(Post $post): void
    {
        $this->postId = $post->id;
        $this->loadBoard();
    }

    private function election(): ?Election
    {
        return Election::with(['candidates', 'areas'])->where('post_id', $this->postId)->first();
    }

    private function loadBoard(): void
    {
        $election = $this->election();

        if (! $election) {
            $this->editingSetup = false;

            return;
        }

        $this->title = $election->title;
        $this->areaLabel = $election->area_label;
        $this->candidatesText = $election->candidates
            ->map(fn ($candidate) => collect([$candidate->name, $candidate->party, $candidate->color])->filter()->implode(' | '))
            ->implode("\n");
        $this->areasText = $election->areas->pluck('name')->implode("\n");

        $byArea = $election->votesByArea();
        foreach ($election->areas as $area) {
            $this->reported[$area->id] = $area->reported;
            foreach ($election->candidates as $candidate) {
                $this->votes[$area->id][$candidate->id] = $byArea[$area->id][$candidate->id] ?? 0;
            }
        }
    }

    public function startSetup(): void
    {
        if (! $this->election()) {
            $this->title = $this->title ?: Post::findOrFail($this->postId)->title;
        }

        $this->editingSetup = true;
    }

    public function cancelSetup(): void
    {
        $this->editingSetup = false;
        $this->resetValidation();
        $this->loadBoard();
    }

    public function saveSetup(): void
    {
        $candidates = $this->lines($this->candidatesText);
        $areas = $this->lines($this->areasText);

        $this->validate([
            'title' => 'required|string|max:160',
            'areaLabel' => 'nullable|string|max:40',
            'candidatesText' => ['required', 'string', 'max:3000', function ($attribute, $value, $fail) use ($candidates) {
                if (count($candidates) < 2) {
                    $fail('Add at least 2 candidates, one per line.');
                }
                if (count($candidates) > 12) {
                    $fail('Up to 12 candidates.');
                }
            }],
            'areasText' => ['required', 'string', 'max:6000', function ($attribute, $value, $fail) use ($areas) {
                if (count($areas) > 100) {
                    $fail('Up to 100 areas.');
                }
            }],
        ], [
            'candidatesText.required' => 'Add the candidates, one per line.',
            'areasText.required' => 'Add the areas results come from, one per line.',
        ]);

        Election::setUp(Post::findOrFail($this->postId), trim($this->title), trim($this->areaLabel), $candidates, $areas);

        $this->editingSetup = false;
        $this->votes = [];
        $this->reported = [];
        $this->loadBoard();
        $this->toast('Results board saved. Now type in the votes for each area.');
    }

    public function saveArea(int $areaId): void
    {
        $election = $this->election();
        abort_unless($election, 404);

        $this->validate(
            ["votes.{$areaId}.*" => 'nullable|integer|min:0|max:50000000'],
            ["votes.{$areaId}.*.integer" => 'Votes must be whole numbers.']
        );

        $area = ElectionArea::where('election_id', $election->id)->findOrFail($areaId);
        $election->recordArea($area, $this->votes[$areaId] ?? [], (bool) ($this->reported[$areaId] ?? false));

        $this->toast($area->name.' saved.');
    }

    public function toggleFinal(): void
    {
        $election = $this->election();
        abort_unless($election, 404);

        $election->update(['is_final' => ! $election->is_final]);
        $this->toast($election->is_final ? 'Marked as final results.' : 'Back to counting.');
    }

    public function deleteBoard(): void
    {
        optional($this->election())->deleteBoard();

        $this->reset('title', 'candidatesText', 'areasText', 'votes', 'reported', 'editingSetup');
        $this->areaLabel = 'LGA';
        $this->toast('Results board removed.');
    }

    /**
     * @return array<int, string>
     */
    private function lines(string $text): array
    {
        return collect(preg_split('/\R/', $text))
            ->map(fn ($line) => trim(preg_replace('/\s+/', ' ', $line)))
            ->filter(fn ($line) => trim(explode('|', $line)[0]) !== '')
            ->unique(fn ($line) => mb_strtolower(trim(explode('|', $line)[0])))
            ->values()
            ->all();
    }

    public function render(): View
    {
        $election = $this->election();
        $votesByArea = $election ? $election->votesByArea() : [];

        return view('livewire.admin.election-results-manager', [
            'election' => $election,
            'standings' => $election ? $election->standings($votesByArea) : collect(),
            'votesByArea' => $votesByArea,
        ]);
    }
}
