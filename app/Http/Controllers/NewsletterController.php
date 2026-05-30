<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // prevent duplicates safely
        $subscriber = Subscriber::where('email', $request->email)->first();

        if ($subscriber) {
            if ($subscriber->is_active) {
                return back()->with('success', 'You are already subscribed!');
            }

            $subscriber->is_active = true;
            $subscriber->unsubscribe_token = Str::random(32);
            $subscriber->save();

            return back()->with('success', 'Subscription reactivated!');
        }

        Subscriber::create([
            'email' => $request->email,
            'unsubscribe_token' => Str::random(32),
            'is_active' => true,
        ]);

        return back()->with('success', 'You have successfully subscribed!');
    }

    public function unsubscribe($token)
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->first();

        if (!$subscriber) {
            return redirect('/')->with('error', 'Invalid unsubscribe link.');
        }

        $subscriber->is_active = false;
        $subscriber->save();

        return redirect('/')->with('success', 'You have successfully unsubscribed.');
    }
}