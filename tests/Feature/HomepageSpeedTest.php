<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomepageSpeedTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->admin = User::factory()->create(['password' => 'password']);
        $this->category = Category::firstOrCreate(['name' => 'Zz Speed Category']);
    }

    private function makePost(string $title, array $attributes = []): Post
    {
        return Post::create(array_merge([
            'admin_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => $title,
            'content' => '<p>Body</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => 'published',
        ], $attributes));
    }

    private function countQueries(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $callback();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    public function test_a_cached_homepage_barely_touches_the_database(): void
    {
        foreach (range(1, 8) as $i) {
            $post = $this->makePost("Zz Speed Story {$i}");
            Comment::create(['post_id' => $post->id, 'user_id' => $this->admin->id, 'content' => 'Hi', 'approved_at' => now()]);
        }

        $first = $this->countQueries(fn () => $this->get(url('/'))->assertOk()->assertSee('Zz Speed Story 8'));
        $second = $this->countQueries(fn () => $this->get(url('/'))->assertOk()->assertSee('Zz Speed Story 8'));

        $this->assertLessThan($first, $second);
        $this->assertLessThanOrEqual(3, $second, 'cached visits should not query per card');
    }

    public function test_cards_show_comment_and_like_counts_without_one_query_per_card(): void
    {
        $post = $this->makePost('Zz Counted Story');
        Comment::create(['post_id' => $post->id, 'user_id' => $this->admin->id, 'content' => 'One', 'approved_at' => now()]);
        Comment::create(['post_id' => $post->id, 'user_id' => $this->admin->id, 'content' => 'Pending']);
        $post->likedByUsers()->attach($this->admin->id);

        $loaded = Post::withCount(['comments', 'likedByUsers'])->find($post->id);

        $queries = $this->countQueries(function () use ($loaded) {
            $this->assertSame(1, $loaded->comments_count, 'only approved comments count');
            $this->assertSame(1, $loaded->liked_by_users_count);
        });
        $this->assertSame(0, $queries);
    }

    public function test_publishing_or_editing_a_post_shows_on_the_homepage_at_once(): void
    {
        $this->get(url('/'))->assertOk();

        $post = $this->makePost('Zz Fresh Off The Press');
        $this->get(url('/'))->assertOk()->assertSee('Zz Fresh Off The Press');

        $post->update(['title' => 'Zz Headline Was Changed']);
        $this->get(url('/'))->assertOk()->assertSee('Zz Headline Was Changed')->assertDontSee('Zz Fresh Off The Press');

        $post->delete();
        $this->get(url('/'))->assertOk()->assertDontSee('Zz Headline Was Changed');
    }

    public function test_homepage_shows_each_category_with_its_six_newest_stories_only(): void
    {
        foreach (range(1, 9) as $i) {
            $post = $this->makePost("Zz Cat Story {$i}");
            $post->forceFill(['created_at' => now()->addMinutes($i)])->saveQuietly();
        }

        $this->get(url('/'))->assertOk()->assertViewHas('categoriesWithPosts', function ($categories) {
            $posts = $categories->firstWhere('id', $this->category->id)->posts;

            return $posts->count() === 6 && $posts->first()->title === 'Zz Cat Story 9';
        });
    }

    public function test_like_button_remembers_what_the_reader_liked(): void
    {
        $liked = $this->makePost('Zz Liked By Reader');
        $reader = User::factory()->create(['password' => 'password']);
        $liked->likedByUsers()->attach($reader->id);

        $this->get(url('/'))->assertOk();

        $this->actingAs($reader)->get(url('/'))->assertOk()
            ->assertViewHas('likedPostIds', fn ($ids) => in_array($liked->id, $ids, true))
            ->assertSee('liked: true', false);
    }

    public function test_card_share_menus_are_built_when_opened(): void
    {
        $this->makePost('Zz Share Story');

        $html = $this->get(url('/'))->assertOk()->getContent();

        $this->assertStringContainsString('data-share-lazy', $html);
        $this->assertStringNotContainsString('sharer.php?u='.urlencode(route('posts.show', 'zz-share-story')), $html);
    }

    public function test_opening_a_story_counts_the_view_without_changing_its_modified_time(): void
    {
        $post = $this->makePost('Zz Viewed Story');
        $post->forceFill(['updated_at' => now()->subDay()])->saveQuietly();
        $before = $post->fresh()->updated_at;

        $this->get(route('posts.show', $post))->assertOk();

        $post->refresh();
        $this->assertSame(1, $post->views);
        $this->assertTrue($post->updated_at->equalTo($before));
    }

    public function test_routes_can_be_cached(): void
    {
        $names = collect(app('router')->getRoutes()->getRoutesByName())->keys();

        $this->assertSame($names->count(), $names->unique()->count(), 'two routes with the same name stop route:cache');
        $this->assertTrue(app('router')->has('home'));
    }
}
