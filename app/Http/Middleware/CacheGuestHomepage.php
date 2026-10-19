<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the finished homepage HTML for visitors who aren't logged in, so most visits skip the
 * database and page building and the server answers sooner (the biggest wait on phones).
 *
 * Runs after the session has started, so each visitor still gets their own security token: it is
 * swapped into the saved HTML on the way out. Logged-in readers, pages carrying a one-off message
 * (e.g. "Subscribed!") and any other query string always get a freshly built page. Saved for five
 * minutes, and Post::forgetHomeCache() clears it as soon as a story or category changes.
 */
class CacheGuestHomepage
{
    /**
     * Stands in for the visitor's security token inside the saved HTML.
     */
    private const TOKEN_PLACEHOLDER = '__BM_CSRF_TOKEN__';

    public const SECONDS = 300;

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->cacheKeyFor($request);

        if ($key === null) {
            return $next($request);
        }

        $token = $request->session()->token();
        $html = Cache::get($key);

        if (is_string($html)) {
            return response(str_replace(self::TOKEN_PLACEHOLDER, $token, $html))
                ->header('Content-Type', 'text/html; charset=UTF-8')
                ->header('X-BM-Page-Cache', 'hit');
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200 && is_string($response->getContent()) && $token !== '') {
            Cache::put($key, str_replace($token, self::TOKEN_PLACEHOLDER, $response->getContent()), self::SECONDS);
            $response->headers->set('X-BM-Page-Cache', 'miss');
        }

        return $response;
    }

    /**
     * Which saved copy this request may use, or null when it must get a freshly built page.
     */
    private function cacheKeyFor(Request $request): ?string
    {
        if (! $request->isMethod('GET') || $request->user()) {
            return null;
        }

        // A message from the last action (or form errors) is only meant for this visitor
        if (! empty($request->session()->get('_flash.old')) || $request->session()->has('errors')) {
            return null;
        }

        return match ($request->query()) {
            [] => Post::HOME_CACHE_PREFIX.'page:home',
            ['part' => 'more'] => Post::HOME_CACHE_PREFIX.'page:more',
            default => null,
        };
    }
}
