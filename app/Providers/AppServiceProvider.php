<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Post;
use App\Models\SeoSetting;
use App\Models\Subscriber;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Use Bootstrap 5 pagination
        Paginator::useBootstrap();

        // Site-wide SEO settings (cached; the table may not exist before the first migration)
        View::share('globalSeo', SeoSetting::current());

        // Navigation data for the public header and footer. Both views use it, so it is built
        // once per request, and cached for a minute (cleared when a post or category changes).
        $navigation = null;
        View::composer(['snippets.header', 'snippets.footer'], function ($view) use (&$navigation) {
            $navigation ??= Cache::remember(Post::HOME_CACHE_PREFIX.'nav', 60, function () {
                $allCategories = Category::orderBy('id')->get(['id', 'name', 'slug']);

                return [
                    'navCategories' => $allCategories->take(5),
                    'moreCategories' => $allCategories->skip(5)->values(),
                    'tickerPosts' => Post::where('status', 'published')
                        ->latest()
                        ->take(6)
                        ->get(['id', 'title', 'slug']),
                    // Breaking news replaces the "Latest" ticker while any is active
                    'breakingPosts' => Post::where('status', 'published')
                        ->where('breaking_until', '>', now())
                        ->latest()
                        ->take(6)
                        ->get(['id', 'title', 'slug']),
                ];
            });

            $view->with($navigation);
        });

        // Homepage data (only the homepage needs it). Cached for a minute so busy moments
        // (election night) don't hit the database on every visit; any post change clears it.
        View::composer('welcome', function ($view) {
            $home = Cache::remember(Post::HOME_CACHE_PREFIX.'home', 60, function () {
                // Cards show likes and comments, so count them in the same query
                $forCards = fn () => Post::with(['category', 'admin'])
                    ->withCount(['comments', 'likedByUsers'])
                    ->where('status', 'published');

                // Slider: active headlines (1 day after ticking) first, then the latest stories
                $headlinePosts = $forCards()
                    ->where('headline_until', '>', now())
                    ->latest()
                    ->take(4)
                    ->get();

                $sliderPosts = $headlinePosts->concat(
                    $forCards()
                        ->whereNotIn('id', $headlinePosts->pluck('id'))
                        ->latest()
                        ->take(4 - $headlinePosts->count())
                        ->get()
                )->values();

                // Each category with only the 7 newest stories it shows (1 big + 6 in an even grid) (not every story it has)
                $categoriesWithPosts = Category::whereHas('posts', fn ($query) => $query->where('status', 'published'))
                    ->get()
                    ->each(fn (Category $category) => $category->setRelation('posts', $forCards()
                        ->where('category_id', $category->id)
                        ->latest()
                        ->take(7)
                        ->get()));

                return [
                    'sliderPosts' => $sliderPosts,
                    // Trending: only posts an admin marked with the Trend button (Post::trendingNow)
                    'trendingPosts' => Post::trendingNow(8),
                    'categoriesWithPosts' => $categoriesWithPosts,
                    'mostReadToday' => Post::mostReadToday(5),
                ];
            });

            $view->with($home + [
                // Which of these stories the visitor has liked (not cached: it is per visitor)
                'likedPostIds' => auth()->check()
                    ? auth()->user()->likedPosts()->pluck('posts.id')->all()
                    : [],
            ]);
        });

        View::composer('admin.index', function ($view) {

            // -------------------
            // Basic Stats
            // -------------------
            $totalPosts = Post::count();
            $draftPosts = Post::where('status', 'draft')->count();
            $subscribers = Subscriber::count();
            $views = Post::sum('views');
            $likes = Post::withCount('likedByUsers')->get()->sum('liked_by_users_count');
            $latestPosts = Post::latest()->take(5)->get();

            // -------------------
            // Analytics Charts
            // -------------------

            // Posts per Month (last 12 months)
            $postsPerMonth = Post::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                ->groupBy('month')
                ->pluck('total', 'month');

            // Reads per day for the last 7 days (counted when a story is opened, see post_daily_views)
            $dailyReads = DB::table('post_daily_views')
                ->where('date', '>=', now()->subDays(6)->toDateString())
                ->groupBy('date')
                ->selectRaw('date, SUM(views) as total')
                ->pluck('total', 'date');

            $viewsPerDay = collect(range(6, 0))->map(function (int $daysAgo) use ($dailyReads) {
                $day = now()->subDays($daysAgo);

                return (object) [
                    'date' => $day->format('D j M'),
                    'total' => (int) ($dailyReads[$day->toDateString()] ?? 0),
                ];
            });

            // Subscriber Growth (last 7 days)
            $subscriberGrowth = Subscriber::selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->take(7)
                ->get();

            // Trending now: posts an admin marked with the Trend button (Post::trendingNow)
            $trendingPosts = Post::trendingNow(5);

            // Share all with admin dashboard view
            $view->with([
                'totalPosts' => $totalPosts,
                'draftPosts' => $draftPosts,
                'subscribers' => $subscribers,
                'views' => $views,
                'likes' => $likes,
                'latestPosts' => $latestPosts,
                'postsPerMonth' => $postsPerMonth,
                'viewsPerDay' => $viewsPerDay,
                'subscriberGrowth' => $subscriberGrowth,
                'trendingPosts' => $trendingPosts,
            ]);
        });
    }
}
