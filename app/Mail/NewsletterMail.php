<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $latestPosts;
    public $trendingPosts;
    public $subscriber;

    public function __construct($latestPosts, $trendingPosts, $subscriber)
    {
        $this->latestPosts = $latestPosts;
        $this->trendingPosts = $trendingPosts;
        $this->subscriber = $subscriber;
    }

    public function build()
    {
        return $this->subject('🔥 BerifMedia Weekly Newsletter')
                    ->markdown('emails.newsletter');
    }
}