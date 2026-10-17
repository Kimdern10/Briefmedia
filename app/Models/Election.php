<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Election results board inside a story: totals per candidate plus a table per area (LGA).
 */
class Election extends Model
{
    protected $fillable = ['post_id', 'title', 'area_label', 'is_final'];

    protected $casts = ['is_final' => 'boolean'];

    /** Colours given to candidates when the admin doesn't pick one. */
    public const COLORS = ['#1f8a4c', '#2f5bd3', '#d9342b', '#f0a202', '#7b3fd1', '#0e9aa7', '#d6457f', '#5c6470'];

    /**
     * "LGA" / "LGAs", "Ward" / "Wards". Abbreviations just get a lower-case "s".
     */
    public function areaLabelFor(int $count): string
    {
        $label = $this->area_label ?: 'LGA';

        if ($count === 1) {
            return $label;
        }

        return mb_strtoupper($label) === $label ? $label.'s' : Str::plural($label);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(ElectionCandidate::class)->orderBy('position');
    }

    public function areas(): HasMany
    {
        return $this->hasMany(ElectionArea::class)->orderBy('position');
    }

    /**
     * Votes keyed by area id, then candidate id.
     *
     * @return array<int, array<int, int>>
     */
    public function votesByArea(): array
    {
        $votes = [];

        ElectionVote::whereIn('election_area_id', $this->areas->pluck('id'))
            ->get(['election_area_id', 'election_candidate_id', 'votes'])
            ->each(function (ElectionVote $vote) use (&$votes) {
                $votes[$vote->election_area_id][$vote->election_candidate_id] = (int) $vote->votes;
            });

        return $votes;
    }

    /**
     * Candidates with `total` votes and `percent` of all votes, leader first.
     *
     * @param  array<int, array<int, int>>  $votesByArea
     * @return Collection<int, ElectionCandidate>
     */
    public function standings(array $votesByArea): Collection
    {
        $totals = [];
        foreach ($votesByArea as $areaVotes) {
            foreach ($areaVotes as $candidateId => $count) {
                $totals[$candidateId] = ($totals[$candidateId] ?? 0) + $count;
            }
        }

        $allVotes = array_sum($totals);

        return $this->candidates
            ->each(function (ElectionCandidate $candidate) use ($totals, $allVotes) {
                $candidate->total = $totals[$candidate->id] ?? 0;
                $candidate->percent = $allVotes > 0 ? round($candidate->total / $allVotes * 100, 1) : 0;
            })
            ->sortByDesc('total')
            ->values();
    }

    /**
     * Save the votes for one area and whether it has finished reporting.
     *
     * @param  array<int|string, int|string|null>  $votes  candidate id => votes
     */
    public function recordArea(ElectionArea $area, array $votes, bool $reported): void
    {
        abort_unless($area->election_id === $this->id, 404);

        $candidateIds = $this->candidates()->pluck('id')->all();

        DB::transaction(function () use ($area, $votes, $reported, $candidateIds) {
            foreach ($candidateIds as $candidateId) {
                ElectionVote::updateOrCreate(
                    ['election_area_id' => $area->id, 'election_candidate_id' => $candidateId],
                    ['votes' => max(0, (int) ($votes[$candidateId] ?? 0))]
                );
            }

            $area->update(['reported' => $reported]);
            $this->touch();
        });
    }

    /**
     * Create or update the board from the admin form. Candidates and areas are matched by
     * name, so renaming nothing keeps every vote already entered.
     *
     * Candidate lines: "Charles Soludo | APGA | #1f8a4c" (party and colour optional).
     *
     * @param  array<int, string>  $candidateLines
     * @param  array<int, string>  $areaNames
     */
    public static function setUp(Post $post, string $title, string $areaLabel, array $candidateLines, array $areaNames): self
    {
        return DB::transaction(function () use ($post, $title, $areaLabel, $candidateLines, $areaNames) {
            $election = static::updateOrCreate(['post_id' => $post->id], [
                'title' => $title,
                'area_label' => $areaLabel ?: 'LGA',
            ]);

            $existing = $election->candidates()->get()->keyBy(fn ($candidate) => mb_strtolower($candidate->name));
            $keep = [];

            foreach (array_values($candidateLines) as $position => $line) {
                $parts = array_map('trim', explode('|', $line));
                $name = $parts[0];
                $color = collect($parts)->first(fn ($part) => preg_match('/^#[0-9a-f]{6}$/i', $part));
                $party = collect(array_slice($parts, 1))->first(fn ($part) => $part !== '' && $part !== $color);

                $candidate = $existing->get(mb_strtolower($name)) ?? new ElectionCandidate(['election_id' => $election->id]);
                $candidate->fill([
                    'name' => $name,
                    'party' => $party ? mb_strtoupper(mb_substr($party, 0, 30)) : null,
                    'color' => $color ?: ($candidate->color && $candidate->exists ? $candidate->color : self::COLORS[$position % count(self::COLORS)]),
                    'position' => $position,
                ])->save();
                $keep[] = $candidate->id;
            }

            $removed = $election->candidates()->whereNotIn('id', $keep)->pluck('id');
            ElectionVote::whereIn('election_candidate_id', $removed)->delete();
            ElectionCandidate::whereIn('id', $removed)->delete();

            $existingAreas = $election->areas()->get()->keyBy(fn ($area) => mb_strtolower($area->name));
            $keepAreas = [];

            foreach (array_values($areaNames) as $position => $name) {
                $area = $existingAreas->get(mb_strtolower($name)) ?? new ElectionArea(['election_id' => $election->id]);
                $area->fill(['name' => $name, 'position' => $position])->save();
                $keepAreas[] = $area->id;
            }

            $removedAreas = $election->areas()->whereNotIn('id', $keepAreas)->pluck('id');
            ElectionVote::whereIn('election_area_id', $removedAreas)->delete();
            ElectionArea::whereIn('id', $removedAreas)->delete();

            return $election->fresh(['candidates', 'areas']);
        });
    }

    /**
     * Remove the board and every vote on it.
     */
    public function deleteBoard(): void
    {
        DB::transaction(function () {
            ElectionVote::whereIn('election_area_id', $this->areas()->pluck('id'))->delete();
            $this->areas()->delete();
            $this->candidates()->delete();
            $this->delete();
        });
    }
}
