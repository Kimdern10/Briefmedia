<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // /home is open to everyone: guests and readers both land on the news
        $this->middleware('auth')->only('logout');
    }

    /**
     * Old /home address (sign-in and sign-up used to land here): sends everyone to the news homepage.
     */
    public function index(Request $request): RedirectResponse
    {
        // Keep a message like "Logged in successfully" for the homepage
        $request->session()->reflash();

        return redirect()->route('home');
    }

    public function logout()
    {
        Auth::guard('web')->logout();

        return redirect('/')->with('message', 'you have successfully logged out');
    }
}
