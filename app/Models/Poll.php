<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * A reader poll shown inside an article ("What do you think?").
 */
class Poll extends Model
{
    protected $fillable = ['post_id', 'question'];

    public const MAX_OPTIONS = 6;

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('position');
    }

    public function totalVotes(): int
    {
        return (int) $this->options->sum('votes');
    }

    public function hasVoted(string $voterKey): bool
    {
        return DB::table('poll_votes')->where('poll_id', $this->id)->where('voter_key', $voterKey)->exists();
    }

    /**
     * Record one vote. Returns false when this voter already voted or the option isn't in this poll.
     */
    public function vote(int $optionId, string $voterKey, ?int $userId = null): bool
    {
        if (! $this->options()->whereKey($optionId)->exists()) {
            return false;
        }

        $recorded = DB::transaction(function () use ($optionId, $voterKey, $userId) {
            $inserted = DB::table('poll_votes')->insertOrIgnore([
                'poll_id' => $this->id,
                'poll_option_id' => $optionId,
                'user_id' => $userId,
                'voter_key' => $voterKey,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($inserted === 0) {
                return false;
            }

            PollOption::whereKey($optionId)->increment('votes');

            return true;
        });

        if ($recorded) {
            // Guest article pages are cached, so retire their copies when standings change.
            Post::forgetHomeCache();
        }

        return $recorded;
    }

    /**
     * Create, update or remove the poll for $post from the post form.
     * Options keep their votes when their wording stays the same.
     *
     * @param  array<int, string>  $labels
     */
    public static function syncFor(Post $post, ?string $question, array $labels): void
    {
        $question = trim((string) $question);
        $labels = collect($labels)->map(fn ($label) => trim((string) $label))->filter()->unique()->take(self::MAX_OPTIONS)->values();

        if ($question === '' || $labels->count() < 2) {
            $existing = static::where('post_id', $post->id)->first();
            if ($existing) {
                DB::table('poll_votes')->where('poll_id', $existing->id)->delete();
                $existing->options()->delete();
                $existing->delete();
            }

            return;
        }

        $poll = static::updateOrCreate(['post_id' => $post->id], ['question' => $question]);
        $current = $poll->options()->get()->keyBy('label');

        foreach ($labels as $position => $label) {
            $option = $current->pull($label) ?? new PollOption(['poll_id' => $poll->id, 'label' => $label]);
            $option->position = $position;
            $option->save();
        }

        // Options that were removed take their votes with them
        foreach ($current as $removed) {
            DB::table('poll_votes')->where('poll_option_id', $removed->id)->delete();
            $removed->delete();
        }
    }
}
