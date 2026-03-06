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
            'email' => 'required|email|unique:subscribers,email',
        ]);

        Subscriber::create([
            'email' => $request->email,
            'unsubscribe_token' => Str::random(32),
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