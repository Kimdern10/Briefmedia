<?php

namespace App\Livewire;

use App\Models\Poll;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Reader poll inside an article. One vote per account, or per device for visitors
 * who aren't logged in. Results show as soon as you vote.
 */
class PostPoll extends Component
{
    #[Locked]
    public int $pollId;

    public ?int $choice = null;

    public bool $voted = false;

    public function mount(Poll $poll): void
    {
        $this->pollId = $poll->id;
        $this->voted = $poll->hasVoted($this->voterKey());
    }

    public function vote(): void
    {
        $this->validate(['choice' => 'required|integer'], ['choice.required' => 'Pick an answer first.']);

        $rateKey = 'poll-vote:'.request()->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 20)) {
            $this->addError('choice', 'Too many votes from this connection. Try again later.');

            return;
        }
        RateLimiter::hit($rateKey, 3600);

        $poll = Poll::findOrFail($this->pollId);
        $poll->vote($this->choice, $this->voterKey(), auth()->id());
        $this->voted = true;
    }

    /**
     * Who is voting: the account when logged in, otherwise this browser (session + device).
     */
    private function voterKey(): string
    {
        if (auth()->check()) {
            return 'user:'.auth()->id();
        }

        $device = session()->get('poll_device');
        if (! $device) {
            $device = hash('sha256', request()->ip().'|'.request()->userAgent());
            session()->put('poll_device', $device);
        }

        return 'guest:'.substr($device, 0, 64);
    }

    public function render(): View
    {
        $poll = Poll::with('options')->findOrFail($this->pollId);

        return view('livewire.post-poll', [
            'poll' => $poll,
            'total' => $poll->totalVotes(),
        ]);
    }
}
