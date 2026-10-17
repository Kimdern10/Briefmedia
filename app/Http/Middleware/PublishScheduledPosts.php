<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Publishes scheduled posts that are due, at most once a minute, while people browse.
 * This makes scheduling work on hosting without a cron job; with cron set up,
 * `posts:publish-scheduled` does the same job on time even when nobody visits.
 */
class PublishScheduledPosts
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Cache::add('posts:publish-scheduled:checked', true, now()->addMinute())) {
            try {
                Post::publishDueScheduled();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $next($request);
    }
}
