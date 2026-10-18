<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin forms are sent in the background (bm-navigate.js) so saving doesn't reload the page.
 * For those requests a redirect ("saved, go to the posts list" or "go back, fix these errors")
 * is answered with its address as JSON; the browser then opens that page with Livewire
 * navigation, where the flash message, errors and old input show as usual.
 */
class RedirectsForBackgroundForms
{
    public const HEADER = 'X-BM-Background-Form';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->headers->has(self::HEADER) && $response instanceof RedirectResponse) {
            return response()->json(['redirect' => $response->getTargetUrl()]);
        }

        return $response;
    }
}
