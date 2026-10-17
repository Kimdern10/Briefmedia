<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    /**
     * @var string
     */
    protected $signature = 'posts:publish-scheduled';

    /**
     * @var string
     */
    protected $description = 'Publish scheduled posts whose publish time has passed';

    public function handle(): int
    {
        $published = Post::publishDueScheduled();

        $this->info($published === 0 ? 'No scheduled posts are due.' : "Published {$published} scheduled post(s).");

        return self::SUCCESS;
    }
}
