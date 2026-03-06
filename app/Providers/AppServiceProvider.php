<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\Category;
use App\Models\SeoSetting;
use App\Models\Subscriber;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Use Bootstrap 5 pagination 
        Paginator::useBootstrap();
        
        
             $seo = SeoSetting::first();
        View::share('globalSeo', $seo);

        View::composer('*', function ($view) {

            // Slider posts (8 latest)
            $sliderPosts = Post::with(['category', 'admin'])
        ->withCount('comments')
        ->where('status', 'published')
        ->latest()
        ->take(4)
        ->get();

            // Trending posts
            $trendingPosts = Post::with(['category', 'likedByUsers'])
                ->withCount(['comments', 'likedByUsers as likes_count'])
                ->where('status', 'published')
                ->orderByDesc('views')
                ->orderByDesc('likes_count')
                ->orderByDesc('comments_count')
                ->take(8)
                ->get();

            // Latest 6 posts for EACH category
          $categoriesWithPosts = Category::with(['posts' => function ($query) {
    $query->where('status', 'published')
          ->latest(); // do NOT use take() here
}])
->whereHas('posts', function ($query) {
    $query->where('status', 'published');
})
->get();

            $view->with([
                'sliderPosts'   => $sliderPosts,
                'trendingPosts' => $trendingPosts,
                'categoriesWithPosts'    => $categoriesWithPosts, 
            ]);
        });



          View::composer('admin.index', function ($view) {


    // -------------------
    // Basic Stats
    // -------------------
    $totalPosts   = Post::count();
    $draftPosts   = Post::where('status', 'draft')->count();
    $subscribers  = Subscriber::count();
    $views        = Post::sum('views');
    $likes        = Post::withCount('likedByUsers')->get()->sum('liked_by_users_count');
    $latestPosts  = Post::latest()->take(5)->get();

    // -------------------
    // Analytics Charts
    // -------------------

    // Posts per Month (last 12 months)
    $postsPerMonth = Post::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
        ->groupBy('month')
        ->pluck('total','month');

    // Views per Day (last 7 days)
    $viewsPerDay = Post::selectRaw('DATE(created_at) as date, SUM(views) as total')
        ->groupBy('date')
        ->orderBy('date')
        ->take(7)
        ->get();

    // Subscriber Growth (last 7 days)
    $subscriberGrowth = Subscriber::selectRaw('DATE(created_at) as date, COUNT(*) as total')
        ->groupBy('date')
        ->orderBy('date')
        ->take(7)
        ->get();

    // Most Viewed Posts
    $mostViewedPosts = Post::orderByDesc('views')->take(5)->get();

    // Share all with admin dashboard view
    $view->with([
        'totalPosts'       => $totalPosts,
        'draftPosts'       => $draftPosts,
        'subscribers'      => $subscribers,
        'views'            => $views,
        'likes'            => $likes,
        'latestPosts'      => $latestPosts,
        'postsPerMonth'    => $postsPerMonth,
        'viewsPerDay'      => $viewsPerDay,
        'subscriberGrowth' => $subscriberGrowth,
        'mostViewedPosts'  => $mostViewedPosts,
    ]);
});
    }
    }
