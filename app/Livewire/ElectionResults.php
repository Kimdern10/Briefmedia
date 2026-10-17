<?php

namespace App\Livewire;

use App\Models\Election;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Election results board inside a story. While counting it refreshes every 15 seconds,
 * so readers see new results without reloading.
 */
class ElectionResults extends Component
{
    #[Locked]
    public int $electionId;

    public bool $showAllAreas = false;

    public function mount(Election $election): void
    {
        $this->electionId = $election->id;
    }

    public function toggleAreas(): void
    {
        $this->showAllAreas = ! $this->showAllAreas;
    }

    public function render(): View
    {
        $election = Election::with(['candidates', 'areas'])->findOrFail($this->electionId);
        $votesByArea = $election->votesByArea();

        return view('livewire.election-results', [
            'election' => $election,
            'standings' => $election->standings($votesByArea),
            'votesByArea' => $votesByArea,
            'reportedCount' => $election->areas->where('reported', true)->count(),
            'totalVotes' => array_sum(array_map('array_sum', $votesByArea)),
        ]);
    }
}
