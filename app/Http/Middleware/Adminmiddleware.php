<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Adminmiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (int) $user->role_as !== 1) {
            if ($request->expectsJson()) {
                abort(403, 'You are not allowed to access the admin area.');
            }

            return redirect('/')->with('error', 'You are not allowed to access the admin area.');
        }

        return $next($request);
    }
}
