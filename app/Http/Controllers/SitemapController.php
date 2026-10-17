<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\SeoSetting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Sitemaps generated from the database, so Google finds new stories straight away.
 *  - /sitemap.xml       every published post, category and page
 *  - /news-sitemap.xml  stories from the last 2 days (Google News format)
 *  - /robots.txt        points crawlers at both sitemaps
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addMinutes(10), function () {
            $posts = Post::query()
                ->where('status', 'published')
                ->where('no_index', false)
                ->latest('updated_at')
                ->limit(5000)
                ->get(['slug', 'updated_at', 'created_at', 'featured_image_1', 'title']);

            $categories = Category::query()->get(['slug', 'updated_at']);

            return view('sitemaps.index', compact('posts', 'categories'))->render();
        });

        return $this->xml($xml);
    }

    public function news(): Response
    {
        $xml = Cache::remember('news-sitemap.xml', now()->addMinutes(5), function () {
            $posts = Post::query()
                ->where('status', 'published')
                ->where('no_index', false)
                ->where('created_at', '>=', now()->subDays(2))
                ->latest()
                ->limit(1000)
                ->get(['slug', 'title', 'created_at', 'meta_keywords']);

            return view('sitemaps.news', [
                'posts' => $posts,
                'publicationName' => optional(SeoSetting::first())->site_name ?: 'BriefMedia',
            ])->render();
        });

        return $this->xml($xml);
    }

    public function robots(): Response
    {
        $body = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /user/',
            'Disallow: /livewire/',
            '',
            'Sitemap: '.url('/sitemap.xml'),
            'Sitemap: '.url('/news-sitemap.xml'),
            '',
        ]);

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function xml(string $xml): Response
    {
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
