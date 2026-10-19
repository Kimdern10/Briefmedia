<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\AutoTagger;
use Illuminate\Console\Command;

/**
 * Gives tags to stories that don't have any yet, picked from each story's own words
 * (see AutoTagger). Stories that already have tags are left alone. Safe to run again.
 */
class AutoTagPosts extends Command
{
    /**
     * @var string
     */
    protected $signature = 'tags:auto
                            {--limit=500 : How many stories to tag in one run}
                            {--dry-run : Only show the tags that would be added}';

    /**
     * @var string
     */
    protected $description = 'Add automatic tags to published stories that have none';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');

        $posts = Post::where('status', 'published')
            ->doesntHave('tags')
            ->latest()
            ->take($limit)
            ->get();

        $tagged = 0;

        foreach ($posts as $post) {
            $names = $dryRun ? AutoTagger::suggest($post) : AutoTagger::apply($post);

            if ($names === []) {
                $this->line("#{$post->id} ".mb_strimwidth($post->title, 0, 60, '…').'  (no tags found)');

                continue;
            }

            $tagged++;
            $this->line("#{$post->id} ".mb_strimwidth($post->title, 0, 60, '…').'  →  '.implode(', ', $names));
        }

        if (! $dryRun && $tagged > 0) {
            Post::forgetHomeCache();
        }

        $remaining = Post::where('status', 'published')->doesntHave('tags')->count();

        $this->newLine();
        $this->info($dryRun ? "{$tagged} stor(ies) would get tags." : "{$tagged} stor(ies) tagged.");
        if (! $dryRun) {
            $this->info($remaining > 0 ? "{$remaining} published stor(ies) still have no tags (none could be found, or run again)." : 'Every published story has tags.');
        }

        return self::SUCCESS;
    }
}
