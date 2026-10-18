<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsOptionsTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();                 // the post store action also shares to Facebook
        Storage::fake('public');

        $this->admin = User::factory()->create(['password' => 'password', 'name' => 'Desk Admin']);
        $this->admin->forceFill(['role_as' => 1])->save();
        $this->category = Category::firstOrCreate(['name' => 'Zz News Options Category']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function formData(array $overrides = []): array
    {
        return array_merge([
            'category_id' => $this->category->id,
            'title' => 'Zz Governor Visits Awka Market',
            'excerpt' => 'Short summary',
            'content' => '<p>Full story</p>',
            'status' => 'published',
            'featured_image_1' => UploadedFile::fake()->image('cover.jpg'),
        ], $overrides);
    }

    private function makePost(array $attributes = []): Post
    {
        return Post::create(array_merge([
            'admin_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'Zz Existing Story',
            'excerpt' => 'Excerpt',
            'content' => '<p>Body</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => 'published',
        ], $attributes));
    }

    public function test_video_links_become_safe_embed_addresses(): void
    {
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', Post::embedUrlFor('https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10s'));
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', Post::embedUrlFor('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertSame('https://www.youtube-nocookie.com/embed/abcDEF12345', Post::embedUrlFor('https://youtube.com/shorts/abcDEF12345'));
        $this->assertSame('https://player.vimeo.com/video/76979871', Post::embedUrlFor('https://vimeo.com/76979871'));
        $this->assertStringStartsWith('https://www.facebook.com/plugins/video.php?', Post::embedUrlFor('https://www.facebook.com/BriefMediablog/videos/1234567890'));

        $this->assertNull(Post::embedUrlFor('https://evil.example.com/video'));
        $this->assertNull(Post::embedUrlFor('javascript:alert(1)'));
        $this->assertNull(Post::embedUrlFor(''));
    }

    public function test_admin_can_create_breaking_headline_video_post_with_author_name(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $this->formData([
                'author_name' => 'Chioma Agbomma',
                'is_headline' => '1',
                'is_breaking' => '1',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ]))
            ->assertRedirect(route('admin.posts.index'))
            ->assertSessionHasNoErrors();

        $post = Post::where('title', 'Zz Governor Visits Awka Market')->firstOrFail();

        $this->assertSame('Chioma Agbomma', $post->author_name);
        $this->assertSame('Chioma Agbomma', $post->authorDisplayName());
        $this->assertTrue($post->isHeadline());
        $this->assertTrue($post->headline_until->between(now()->addHours(23), now()->addHours(25)));
        $this->assertTrue($post->isBreaking());
        $this->assertTrue($post->breaking_until->between(now()->addHours(5), now()->addHours(7)), 'breaking lasts 6 hours');
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $post->videoEmbedUrl());
    }

    public function test_options_are_off_by_default_and_author_falls_back_to_the_admin(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $this->formData(['title' => 'Zz Plain Story']))
            ->assertSessionHasNoErrors();

        $post = Post::where('title', 'Zz Plain Story')->firstOrFail();

        $this->assertNull($post->author_name);
        $this->assertSame('Desk Admin', $post->authorDisplayName());
        $this->assertFalse($post->isHeadline());
        $this->assertFalse($post->isBreaking());
        $this->assertNull($post->videoEmbedUrl());
    }

    public function test_unsupported_video_links_are_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $this->formData(['video_url' => 'https://example.com/clip.mp4']))
            ->assertSessionHasErrors('video_url');
    }

    public function test_editing_keeps_the_web_address_unless_the_title_changes(): void
    {
        $post = $this->makePost(['title' => 'Zz Address Story']);
        $originalSlug = $post->slug;

        $this->actingAs($this->admin)->put(route('admin.posts.update', $post->id), [
            'category_id' => $this->category->id,
            'title' => 'Zz Address Story',
            'content' => '<p>Edited body</p>',
            'status' => 'published',
        ])->assertSessionHasNoErrors();

        $this->assertSame($originalSlug, $post->fresh()->slug);

        $this->actingAs($this->admin)->put(route('admin.posts.update', $post->id), [
            'category_id' => $this->category->id,
            'title' => 'Zz Renamed Address Story',
            'content' => '<p>Edited body</p>',
            'status' => 'published',
        ])->assertSessionHasNoErrors();

        $this->assertSame('zz-renamed-address-story', $post->fresh()->slug);
    }

    public function test_resaving_keeps_the_breaking_window_and_unticking_ends_it(): void
    {
        $breakingUntil = now()->addHours(5)->startOfSecond();
        $post = $this->makePost(['title' => 'Zz Breaking Story', 'breaking_until' => $breakingUntil]);

        $update = fn (array $extra) => $this->actingAs($this->admin)->put(route('admin.posts.update', $post->id), array_merge([
            'category_id' => $this->category->id,
            'title' => 'Zz Breaking Story',
            'content' => '<p>Body</p>',
            'status' => 'published',
        ], $extra));

        $update(['is_breaking' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue($post->fresh()->breaking_until->equalTo($breakingUntil));

        $update(['is_breaking' => '0'])->assertSessionHasNoErrors();
        $this->assertNull($post->fresh()->breaking_until);
    }

    public function test_breaking_bar_headline_slider_video_and_author_appear_on_the_site(): void
    {
        DB::table('posts')->update(['headline_until' => null, 'breaking_until' => null]);

        $post = $this->makePost([
            'title' => 'Zz Breaking Headline Video Story',
            'author_name' => 'Samuel Uzochukwu',
            'headline_until' => now()->addHours(10),
            'breaking_until' => now()->addHours(10),
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ]);
        $post->forceFill(['created_at' => now()->subDays(3)])->save();   // older than the latest posts
        $this->makePost(['title' => 'Zz Newer Ordinary Story']);

        $home = $this->get(url('/'))->assertOk();
        $home->assertSee('bm-topbar--breaking', false)
            ->assertSee('Zz Breaking Headline Video Story')
            ->assertSee('Samuel Uzochukwu');

        // Headline leads the slider even though a newer story exists
        $this->assertMatchesRegularExpression(
            '/bm-hero__slide is-active.*?Zz Breaking Headline Video Story/s',
            $home->getContent()
        );

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('Posted by')
            ->assertSee('Samuel Uzochukwu')
            ->assertSee('bm-chip--breaking', false);
    }

    public function test_headline_and_breaking_both_expire_after_one_day(): void
    {
        DB::table('posts')->update(['headline_until' => null, 'breaking_until' => null]);

        $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $this->formData([
                'title' => 'Zz One Day Story',
                'is_headline' => '1',
                'is_breaking' => '1',
            ]))
            ->assertSessionHasNoErrors();

        $post = Post::where('title', 'Zz One Day Story')->firstOrFail();
        $this->assertTrue($post->isHeadline());
        $this->assertTrue($post->isBreaking());

        $this->travel(Post::HEADLINE_HOURS)->hours();
        $this->travel(1)->minutes();

        $post->refresh();
        $this->assertFalse($post->isHeadline());
        $this->assertFalse($post->isBreaking());
        $this->get(url('/'))->assertOk()->assertDontSee('bm-topbar--breaking', false);
    }

    public function test_breaking_ends_after_6_hours_while_the_headline_stays_for_the_day(): void
    {
        DB::table('posts')->update(['headline_until' => null, 'breaking_until' => null]);

        $post = $this->makePost([
            'title' => 'Zz Six Hour Breaking',
            'breaking_until' => now()->addHours(Post::BREAKING_HOURS),
            'headline_until' => now()->addHours(Post::HEADLINE_HOURS),
        ]);

        $this->assertSame(6, Post::BREAKING_HOURS);
        $this->get(url('/'))->assertOk()->assertSee('bm-topbar--breaking', false);

        $this->travel(6)->hours();
        $this->travel(1)->minutes();
        Cache::flush();

        $post->refresh();
        $this->assertFalse($post->isBreaking());
        $this->assertTrue($post->isHeadline());
        $this->get(url('/'))->assertOk()->assertDontSee('bm-topbar--breaking', false);
    }

    public function test_no_breaking_bar_when_nothing_is_breaking(): void
    {
        DB::table('posts')->update(['breaking_until' => null]);

        $this->get(url('/'))->assertOk()->assertDontSee('bm-topbar--breaking', false);
    }
}
