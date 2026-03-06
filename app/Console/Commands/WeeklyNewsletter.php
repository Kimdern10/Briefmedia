<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use App\Models\Subscriber;
use App\Mail\NewsletterMail;
use Illuminate\Support\Facades\Mail;

class WeeklyNewsletter extends Command
{
    // THIS NAME IS WHAT YOU CALL VIA ARTISAN
    protected $signature = 'newsletter:weekly';
    protected $description = 'Send weekly newsletter with latest and trending news';

    public function handle()
    {
        // Latest 3 posts
        $latestPosts = Post::orderBy('created_at', 'desc')->take(3)->get();

        // Trending 2 posts (most viewed)
        $trendingPosts = Post::orderBy('views', 'desc')->take(2)->get();

        // Active subscribers
        $subscribers = Subscriber::where('is_active', true)->get();

        foreach ($subscribers as $subscriber) {
            Mail::to($subscriber->email)
                ->queue(new NewsletterMail($latestPosts, $trendingPosts, $subscriber));
        }

        $this->info('Weekly newsletter queued successfully for all subscribers!');
    }
}