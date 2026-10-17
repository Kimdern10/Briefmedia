<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Tells the world about a post once it is published:
 *  - shares it to the Facebook page and Telegram channel (once per post, see posts.announced_at)
 *  - sends a breaking-news push alert through OneSignal (once per post, see posts.push_sent_at)
 *
 * Each service is skipped while its keys are missing from .env. Drafts and scheduled posts are
 * never shared; a scheduled post is shared the moment it goes live.
 */
class PostAnnouncer
{
    public static function handle(Post $post): void
    {
        if ($post->status !== 'published' || $post->trashed()) {
            return;
        }

        $share = $post->announced_at === null;
        $push = $post->push_sent_at === null && $post->isBreaking();

        if (! $share && ! $push) {
            return;
        }

        // Mark first so a second save can't send twice
        $marks = array_filter([
            'announced_at' => $share ? now() : null,
            'push_sent_at' => $push ? now() : null,
        ]);
        DB::table('posts')->where('id', $post->id)->update($marks);
        $post->forceFill($marks)->syncOriginalAttributes(array_keys($marks));

        $send = function () use ($post, $share, $push) {
            if ($share) {
                static::shareToFacebook($post);
                static::shareToTelegram($post);
            }
            if ($push) {
                static::sendPushAlert($post);
            }
        };

        // From the admin panel: send after the page has loaded so saving stays fast
        app()->runningInConsole() ? $send() : dispatch($send)->afterResponse();
    }

    public static function shareToFacebook(Post $post): void
    {
        $pageId = config('facebook.page_id');
        $token = config('facebook.access_token');

        if (! $pageId || ! $token) {
            return;
        }

        static::attempt('Facebook', fn () => Http::timeout(10)->post(
            'https://graph.facebook.com/'.config('facebook.graph_version').'/'.$pageId.'/feed',
            [
                'message' => $post->title,
                'link' => route('posts.show', $post),
                'access_token' => $token,
            ]
        ));
    }

    public static function shareToTelegram(Post $post): void
    {
        $token = config('services.telegram.bot_token');
        $channel = config('services.telegram.channel_id');

        if (! $token || ! $channel) {
            return;
        }

        $caption = ($post->isBreaking() ? "🔴 <b>BREAKING</b>\n" : '')
            .'<b>'.e($post->title).'</b>'
            .($post->excerpt ? "\n\n".e(Str::limit($post->excerpt, 300)) : '')
            ."\n\n".route('posts.show', $post);

        static::attempt('Telegram', fn () => $post->featured_image_1
            ? Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendPhoto", [
                'chat_id' => $channel,
                'photo' => $post->shareImageUrl(),
                'caption' => $caption,
                'parse_mode' => 'HTML',
            ])
            : Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $channel,
                'text' => $caption,
                'parse_mode' => 'HTML',
            ]));
    }

    public static function sendPushAlert(Post $post): void
    {
        $appId = config('services.onesignal.app_id');
        $key = config('services.onesignal.rest_api_key');

        if (! $appId || ! $key) {
            return;
        }

        static::attempt('OneSignal', fn () => Http::timeout(10)
            ->withHeaders(['Authorization' => 'Key '.$key])
            ->post('https://api.onesignal.com/notifications', [
                'app_id' => $appId,
                'target_channel' => 'push',
                'included_segments' => ['Total Subscriptions'],
                'headings' => ['en' => '🔴 Breaking news'],
                'contents' => ['en' => Str::limit($post->title, 140)],
                'url' => route('posts.show', $post),
                'chrome_web_image' => $post->featured_image_1 ? $post->shareImageUrl() : null,
            ]));
    }

    /**
     * Run one request and log the outcome; a failing service never breaks saving a post.
     */
    private static function attempt(string $service, callable $request): void
    {
        try {
            $response = $request();

            if ($response->failed()) {
                Log::warning("{$service} share failed", ['status' => $response->status(), 'body' => Str::limit($response->body(), 500)]);
            }
        } catch (Throwable $e) {
            Log::error("{$service} share failed: ".$e->getMessage());
        }
    }
}
