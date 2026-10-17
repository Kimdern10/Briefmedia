<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Post;
use App\Models\SeoSetting;
use App\Models\Subscriber;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Use Bootstrap 5 pagination
        Paginator::useBootstrap();

        // Guard against missing table before migrations have run
        if (Schema::hasTable('seo_settings')) {
            $seo = SeoSetting::first();
            View::share('globalSeo', $seo);
        }

        // Navigation data for the public header and footer
        View::composer(['snippets.header', 'snippets.footer'], function ($view) {
            $allCategories = Category::orderBy('id')->get(['id', 'name', 'slug']);

            $view->with([
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
            ]);
        });

        // Homepage data (only the homepage needs it)
        View::composer('welcome', function ($view) {

            // Slider posts (4 latest)
            // Slider: active headlines (1 day after ticking) first, then the latest stories
            $sliderQuery = fn () => Post::with(['category', 'admin', 'likedByUsers'])
                ->withCount('comments')
                ->where('status', 'published');

            $headlinePosts = $sliderQuery()
                ->where('headline_until', '>', now())
                ->latest()
                ->take(4)
                ->get();

            $sliderPosts = $headlinePosts->concat(
                $sliderQuery()
                    ->whereNotIn('id', $headlinePosts->pluck('id'))
                    ->latest()
                    ->take(4 - $headlinePosts->count())
                    ->get()
            )->values();

            // Trending: only posts an admin marked with the Trend button (Post::trendingNow)
            $trendingPosts = Post::trendingNow(8);

            // Latest 6 posts for EACH category
            $categoriesWithPosts = Category::with(['posts' => function ($query) {
                $query->with(['admin', 'likedByUsers'])
                    ->where('status', 'published')
                    ->latest(); // do NOT use take() here
            }])
                ->whereHas('posts', function ($query) {
                    $query->where('status', 'published');
                })
                ->get();

            $view->with([
                'sliderPosts' => $sliderPosts,
                'trendingPosts' => $trendingPosts,
                'categoriesWithPosts' => $categoriesWithPosts,
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
