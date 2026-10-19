<?php

namespace App\Http\Middleware;

use App\Http\Controllers\User\CategoryPostController;
use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the finished HTML of the homepage, category pages and story pages for visitors who aren't
 * logged in, so most visits skip the database and page building and the server answers sooner.
 *
 * Runs after the session has started, so each visitor still gets their own security token: it is
 * swapped into the saved HTML on the way out. Story views are still counted on every visit (before
 * the saved page is used). Logged-in readers, pages carrying a one-off message (e.g. "Subscribed!")
 * and unknown query strings always get a freshly built page; tracking tags such as Facebook's
 * ?fbclid= are ignored. Saved for five minutes, and Post::forgetHomeCache() (any story, category or
 * comment change) makes every saved page out of date at once.
 */
class CacheGuestPages
{
    /**
     * Stands in for the visitor's security token inside the saved HTML.
     */
    private const TOKEN_PLACEHOLDER = '__BM_CSRF_TOKEN__';

    /**
     * Bumped by Post::forgetHomeCache(); part of every saved page's key, so old copies stop being used.
     */
    public const VERSION_KEY = Post::HOME_CACHE_PREFIX.'pages-version';

    public const SECONDS = 300;

    /**
     * Query parameters added by share links and ads that don't change the page.
     *
     * @var list<string>
     */
    private const TRACKING_PARAMETERS = ['fbclid', 'gclid', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];

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
            if ($request->route()->named('posts.show')) {
                CategoryPostController::recordView($request->route('post'));
            }

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

        $query = array_diff_key($request->query(), array_flip(self::TRACKING_PARAMETERS));
        $route = $request->route();

        $page = match ($route?->getName()) {
            'home' => match ($query) {
                [] => 'home',
                ['part' => 'more'] => 'home-more',
                default => null,
            },
            'category.page' => $this->categoryPage($route->parameter('category')?->id, $query),
            'posts.show' => $query === [] && $route->parameter('post') instanceof Post
                ? 'post:'.$route->parameter('post')->id
                : null,
            default => null,
        };

        return $page === null
            ? null
            : Post::HOME_CACHE_PREFIX.'page:'.Cache::get(self::VERSION_KEY, 1).':'.$page;
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function categoryPage(?int $categoryId, array $query): ?string
    {
        $pageNumber = $query === [] ? '1' : ($query['page'] ?? null);

        if ($categoryId === null || count($query) > 1 || ! is_string($pageNumber) || ! ctype_digit($pageNumber)) {
            return null;
        }

        return "category:{$categoryId}:{$pageNumber}";
    }
}
