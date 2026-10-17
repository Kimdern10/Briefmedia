<?php

namespace App\Livewire;

use App\Models\Subscriber;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Newsletter sign-up box in the site footer. Same rules as NewsletterController::subscribe,
 * but the confirmation appears in place instead of reloading the page.
 */
class NewsletterSignup extends Component
{
    public string $email = '';

    public ?string $confirmation = null;

    public function subscribe(): void
    {
        $this->confirmation = null;

        $this->validate(
            ['email' => 'required|email|max:255'],
            ['email.required' => 'Enter your email address.', 'email.email' => 'Enter a valid email address.'],
        );

        $throttleKey = 'newsletter:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->addError('email', 'Too many attempts. Please try again in a minute.');

            return;
        }

        RateLimiter::hit($throttleKey, 60);

        $email = strtolower(trim($this->email));
        $subscriber = Subscriber::where('email', $email)->first();

        if ($subscriber && $subscriber->is_active) {
            $this->confirmation = "You're already subscribed. Thanks for reading!";
        } elseif ($subscriber) {
            $subscriber->forceFill(['is_active' => true, 'unsubscribe_token' => Str::random(32)])->save();
            $this->confirmation = 'Welcome back! Your subscription is active again.';
        } else {
            Subscriber::create([
                'email' => $email,
                'unsubscribe_token' => Str::random(32),
                'is_active' => true,
            ]);
            $this->confirmation = "You're subscribed! Look out for our next newsletter.";
        }

        $this->reset('email');
    }

    public function render(): View
    {
        return view('livewire.newsletter-signup');
    }
}
