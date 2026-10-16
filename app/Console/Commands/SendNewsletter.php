<?php

namespace App\Console\Commands;

use App\Mail\NewsletterMail;
use App\Models\Post;
use App\Models\Subscriber;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendNewsletter extends Command
{
    // Command name
    protected $signature = 'newsletter:send';

    protected $description = 'Send latest posts to all active newsletter subscribers';

    public function handle()
    {
        // Latest 5 published posts (drafts must never go out by email)
        $posts = Post::with('category')
            ->where('status', 'published')
            ->latest()
            ->take(5)
            ->get();

        // Most viewed published posts from the last 30 days
        $trendingPosts = Post::where('status', 'published')
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('views')
            ->take(5)
            ->get();

        // Stop if no posts
        if ($posts->isEmpty()) {
            $this->info('No posts available to send.');

            return 0;
        }

        // Get all active subscribers
        $subscribers = Subscriber::where('is_active', true)->get();

        if ($subscribers->isEmpty()) {
            $this->info('No active subscribers found.');

            return 0;
        }

        // Send newsletter to each subscriber safely
        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)
                    ->send(new NewsletterMail($posts, $trendingPosts, $subscriber));
            } catch (\Exception $e) {
                // Log any errors instead of breaking the loop
                \Log::error("Failed to send newsletter to {$subscriber->email}: {$e->getMessage()}");
            }
        }

        $this->info('Newsletter sent to '.$subscribers->count().' subscribers.');

        return 0;
    }
}
