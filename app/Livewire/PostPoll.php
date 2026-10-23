<?php

namespace App\Livewire;

use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Reader poll inside an article. One vote per account, or per device for visitors who aren't
 * logged in (remembered for good: the cookie is renewed for 400 days on each visit), and a guest who
 * voted can't vote again after logging in. Admin and staff accounts
 * see the results but can't vote. Results show as soon as you vote.
 */
class PostPoll extends Component
{
    /**
     * Cookie holding a guest's voter ID; only set once they vote, kept 400 days (the browser maximum).
     */
    public const GUEST_VOTER_COOKIE = 'bm_voter';

    /**
     * Where guest voter IDs were kept before the cookie (still read, so earlier voters stay recognised).
     */
    public const GUEST_VOTER_SESSION_KEY = 'poll_device_v2';

    private const GUEST_VOTER_DAYS = 400;

    #[Locked]
    public int $pollId;

    #[Locked]
    public bool $canVote = true;

    public ?int $choice = null;

    public bool $voted = false;

    /**
     * True when this browser has a guest voter ID, i.e. the guest has voted in a poll before.
     */
    public static function guestHasVoterId(Request $request): bool
    {
        return (bool) $request->cookie(self::GUEST_VOTER_COOKIE) || $request->session()->has(self::GUEST_VOTER_SESSION_KEY);
    }

    public function mount(Poll $poll): void
    {
        $this->pollId = $poll->id;
        $this->canVote = ! $this->isStaff();

        // Only look the reader up here; guests get a voter ID when they actually vote (keeps saved pages
        // for guests who never voted identical for everyone, see CacheGuestPages)
        $this->voted = $this->hasAlreadyVoted($poll);
    }

    public function vote(): void
    {
        if (! $this->canVote || $this->isStaff()) {
            return;
        }

        $this->validate(['choice' => 'required|integer'], ['choice.required' => 'Pick an answer first.']);

        $rateKey = 'poll-vote:'.request()->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 20)) {
            $this->addError('choice', 'Too many votes from this connection. Try again later.');

            return;
        }
        RateLimiter::hit($rateKey, 3600);

        $poll = Poll::findOrFail($this->pollId);

        // Closed while the reader had the page open: just show the final results
        if ($poll->isClosed()) {
            return;
        }

        // One vote per person: not again from this account, nor from this browser before logging in
        if (! $this->hasAlreadyVoted($poll)) {
            $voterKey = auth()->check() ? 'user:'.auth()->id() : 'guest:'.$this->guestDevice(create: true);
            $poll->vote($this->choice, $voterKey, auth()->id());
        }

        $this->voted = true;
    }

    /**
     * True when this reader voted in the poll with their account or, as a guest, from this browser.
     */
    private function hasAlreadyVoted(Poll $poll): bool
    {
        $device = $this->guestDevice(create: false);

        return (auth()->check() && $poll->hasVoted('user:'.auth()->id()))
            || ($device !== null && $poll->hasVoted('guest:'.$device));
    }

    /**
     * Admin and staff accounts (roles 1–3) run the site, so they don't vote in reader polls.
     */
    private function isStaff(): bool
    {
        return in_array((string) auth()->user()?->role_as, ['1', '2', '3'], true);
    }

    /**
     * This browser's guest voter ID (null when it has never voted, unless $create gives it one).
     * Every time it's seen, the cookie is renewed for another 400 days, so a returning voter is
     * remembered for good.
     */
    private function guestDevice(bool $create): ?string
    {
        // A random ID per browser: IP + user agent would make everyone on shared Wi-Fi or the same
        // mobile network look like one guest and stop them from voting.
        $device = request()->cookie(self::GUEST_VOTER_COOKIE) ?: session()->get(self::GUEST_VOTER_SESSION_KEY);

        if (! is_string($device) || $device === '') {
            if (! $create) {
                return null;
            }

            $device = Str::random(64);
        }

        $device = substr($device, 0, 64);
        Cookie::queue(self::GUEST_VOTER_COOKIE, $device, 60 * 24 * self::GUEST_VOTER_DAYS);

        return $device;
    }

    public function render(): View
    {
        $poll = Poll::with('options')->findOrFail($this->pollId);

        return view('livewire.post-poll', [
            'poll' => $poll,
            'total' => $poll->totalVotes(),
            'closed' => $poll->isClosed(),
        ]);
    }
}
