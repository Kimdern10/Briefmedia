<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\AdminComponent;
use App\Models\Subscriber;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;

/**
 * "Send newsletter" button for the admin dashboard and subscribers page.
 * Runs the same `newsletter:send` command as the terminal, with a lock so a double
 * click can't email everyone twice.
 */
class NewsletterSender extends Component
{
    use AdminComponent;

    public const LAST_SENT_KEY = 'newsletter:last_sent';

    public function send(): void
    {
        $activeCount = Subscriber::where('is_active', true)->count();

        if ($activeCount === 0) {
            $this->toast('There are no active subscribers to send to.', 'warning');

            return;
        }

        $lock = Cache::lock('newsletter:sending', 600);

        if (! $lock->get()) {
            $this->toast('The newsletter is already being sent. Please wait.', 'warning');

            return;
        }

        try {
            @set_time_limit(0);
            Artisan::call('newsletter:send');
            $output = trim(Artisan::output());

            Cache::forever(self::LAST_SENT_KEY, [
                'at' => now()->toIso8601String(),
                'count' => $activeCount,
                'by' => auth()->user()->name,
            ]);

            Log::info('Newsletter sent from admin', ['admin_id' => auth()->id(), 'subscribers' => $activeCount, 'output' => $output]);
            $this->toast($output !== '' ? $output : "Newsletter sent to {$activeCount} subscribers.");
        } catch (\Throwable $e) {
            report($e);
            $this->toast('Sending failed. Check the email settings and try again.', 'error');
        } finally {
            $lock->release();
        }
    }

    public function render(): View
    {
        $lastSent = Cache::get(self::LAST_SENT_KEY);

        return view('livewire.admin.newsletter-sender', [
            'activeCount' => Subscriber::where('is_active', true)->count(),
            'lastSentAt' => $lastSent ? Carbon::parse($lastSent['at']) : null,
            'lastSentCount' => $lastSent['count'] ?? null,
        ]);
    }
}
