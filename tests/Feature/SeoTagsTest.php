<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\SeoSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class SeoTagsTest extends TestCase
{
    use DatabaseTransactions;

    private Category $category;

    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        SeoSetting::query()->delete();
        // Like the live site: a canonical address and share image saved in Admin → SEO settings
        SeoSetting::create([
            'site_name' => 'BriefMedia',
            'meta_description' => 'Site-wide description.',
            'canonical_url' => 'https://briefmediablog.com',
            'og_image' => 'seo/share.png',
            'robots_meta' => 'index, follow',
        ]);
        // Shared with every view when the app starts, which here was before this setting existed
        View::share('globalSeo', SeoSetting::current());

        $admin = User::factory()->create(['password' => 'password']);
        $this->category = Category::firstOrCreate(['name' => 'Zz Seo Politics'], ['description' => 'Politics from Awka and beyond.']);
        $this->post = Post::create([
            'admin_id' => $admin->id,
            'category_id' => $this->category->id,
            'title' => 'Zz Seo Story',
            'excerpt' => 'A short summary used as the description.',
            'content' => '<p>The story body.</p>',
            'featured_image_1' => 'posts/featured/zz-seo.webp',
            'status' => 'published',
        ]);
    }

    private function canonicalOf(string $html): ?string
    {
        return preg_match('~<link rel="canonical" href="([^"]+)"~', $html, $match) ? $match[1] : null;
    }

    public function test_category_pages_are_their_own_canonical_page_with_their_own_description(): void
    {
        $url = route('category.page', $this->category->slug);
        $html = $this->get($url)->assertOk()->getContent();

        $this->assertSame($url, $this->canonicalOf($html), 'not the homepage');
        $this->assertStringContainsString('<meta name="description" content="Politics from Awka and beyond.">', $html);
        $this->assertStringContainsString('<meta property="og:title" content="Zz Seo Politics News | BriefMedia">', $html);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $html);
        $this->assertStringContainsString('<meta property="og:image" content="'.url('storage/seo/share.png').'">', $html);

        $this->assertSame($url.'?page=2', $this->canonicalOf($this->get($url.'?page=2')->getContent()));
    }

    public function test_homepage_has_a_main_heading_site_search_data_and_big_image_previews(): void
    {
        $html = $this->get(url('/'))->assertOk()->getContent();

        $this->assertSame(url('/'), $this->canonicalOf($html));
        $this->assertMatchesRegularExpression('~<h1 class="bm-visually-hidden">BriefMedia: latest news~', $html);
        $this->assertStringContainsString('"@type":"SearchAction"', $html);
        $this->assertStringContainsString('"@type":"NewsMediaOrganization"', $html);
        $this->assertStringContainsString('<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">', $html);
        $this->assertStringNotContainsString('class="bm-loader"', $html, 'no full-screen loader hiding the page');
    }

    public function test_story_pages_describe_themselves_and_load_their_photo_first(): void
    {
        $html = $this->get(route('posts.show', $this->post))->assertOk()->getContent();

        $this->assertStringContainsString('<title>Zz Seo Story | BriefMedia</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="A short summary used as the description.">', $html);
        $this->assertStringContainsString('max-image-preview:large', $html);
        $this->assertStringContainsString('<link rel="preload" as="image" href="'.$this->post->coverUrl().'" fetchpriority="high">', $html);
        $this->assertSame(route('posts.show', $this->post), $this->canonicalOf($html));
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $html);
    }

    public function test_story_marked_no_index_tells_google(): void
    {
        $this->post->forceFill(['no_index' => true])->saveQuietly();

        $this->get(route('posts.show', $this->post))->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_search_results_are_not_indexed(): void
    {
        $this->get(route('user.posts.search', ['query' => 'Zz Seo']))->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_fonts_and_icons_do_not_block_the_first_paint(): void
    {
        $html = $this->get(url('/'))->assertOk()->getContent();

        $this->assertStringContainsString('rel="preload" as="style" href="https://fonts.googleapis.com', $html);
        $this->assertStringNotContainsString('<link href="https://fonts.googleapis.com/css2', $html);
        $this->assertStringContainsString('window.bmUseStylesheet', $html);
    }
}
