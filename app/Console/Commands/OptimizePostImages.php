<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Shrinks photos uploaded before automatic compression existed: makes a compressed WebP
 * version (max 1600px) of each post photo, plus a small 640px card version and a JPEG copy
 * for link previews for cover photos, and points the post at the new file.
 *
 * Original files are left on the server, so nothing is lost. Safe to run again: photos that
 * are already done are skipped. Runs newest posts first, in batches (--limit).
 */
class OptimizePostImages extends Command
{
    /**
     * @var string
     */
    protected $signature = 'images:optimize
                            {--limit=40 : How many posts to do in one run}
                            {--dry-run : Only report what would be done}';

    /**
     * @var string
     */
    protected $description = 'Compress old post photos to WebP and add small card versions (originals are kept)';

    private const SLOTS = ['featured_image_1', 'featured_image_2', 'featured_image_3', 'featured_image_4'];

    public function handle(): int
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        if (! function_exists('imagewebp')) {
            $this->error('This server\'s PHP can\'t make WebP images (GD without WebP), so nothing was changed.');

            return self::FAILURE;
        }

        $limit = max(1, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');

        $done = 0;
        $before = 0;
        $after = 0;
        $failed = 0;

        $posts = Post::withTrashed()->orderByDesc('id')->get(array_merge(['id', 'title'], self::SLOTS));

        foreach ($posts as $post) {
            if ($done >= $limit) {
                break;
            }

            $work = $this->workFor($post);
            if ($work === []) {
                continue;
            }

            $done++;
            $this->line("#{$post->id} ".mb_strimwidth($post->title, 0, 60, '…'));

            if ($dryRun) {
                foreach ($work as $slot => $task) {
                    $this->line("   {$slot}: {$task}");
                }

                continue;
            }

            $updates = [];
            foreach ($work as $slot => $task) {
                $path = $post->{$slot};

                if ($task === 'add card version') {
                    ImageOptimizer::addCardVersion($path) ? $this->line("   {$slot}: card version added") : $failed++;

                    continue;
                }

                $newPath = ImageOptimizer::optimizeStored($path, $slot === 'featured_image_1');
                if ($newPath === null) {
                    $failed++;
                    $this->warn("   {$slot}: could not be optimised, left as it is");

                    continue;
                }

                $oldSize = (int) $disk->size($path);
                $newSize = (int) $disk->size($newPath);
                $before += $oldSize;
                $after += $newSize;
                $updates[$slot] = $newPath;
                $this->line(sprintf('   %s: %s → %s', $slot, $this->kb($oldSize), $this->kb($newSize)));
            }

            if ($updates !== []) {
                // Straight to the database: no "updated" label, re-sharing or new modified time
                DB::table('posts')->where('id', $post->id)->update($updates);
            }
        }

        if (! $dryRun && $done > 0) {
            Post::forgetHomeCache();
        }

        $remaining = $posts->filter(fn (Post $post) => $this->workFor($post->fresh() ?? $post) !== [])->count();

        $this->newLine();
        $this->info($dryRun ? "{$done} post(s) would be updated." : "{$done} post(s) updated.");
        if ($before > 0) {
            $this->info(sprintf('Photos went from %s to %s (%d%% smaller).', $this->kb($before), $this->kb($after), 100 - (int) round($after / $before * 100)));
        }
        if ($failed > 0) {
            $this->warn("{$failed} photo(s) could not be changed and were left as they are.");
        }
        if (! $dryRun) {
            $this->info($remaining > 0 ? "{$remaining} post(s) still to do: run the command again." : 'All post photos are optimised.');
        }

        return self::SUCCESS;
    }

    /**
     * What needs doing for each photo of a post: optimise an old upload, or add a missing card version.
     *
     * @return array<string, string>
     */
    private function workFor(Post $post): array
    {
        $disk = Storage::disk('public');
        $work = [];

        foreach (self::SLOTS as $slot) {
            $path = $post->{$slot};
            if (! $path || ! $disk->exists($path)) {
                continue;
            }

            if (! preg_match('/\.webp$/i', $path)) {
                // GIFs stay as they are (animation would be lost)
                if (! preg_match('/\.gif$/i', $path)) {
                    $work[$slot] = 'compress to WebP';
                }
            } elseif ($slot === 'featured_image_1' && ! $disk->exists(ImageOptimizer::cardPathFor($path))) {
                $work[$slot] = 'add card version';
            }
        }

        return $work;
    }

    private function kb(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1).' MB' : round($bytes / 1024).' KB';
    }
}
