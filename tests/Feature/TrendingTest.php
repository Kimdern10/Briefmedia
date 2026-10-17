<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Trending only shows posts an admin marked with the 🔥 Trend button.
 * (Picking, expiry and the admin tab are covered in PinnedTrendingTest.)
 */
class TrendingTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Keep the test independent of whatever posts the local database already has.
        DB::table('posts')->update(['views' => 0, 'trending_until' => null]);

        $this->admin = User::factory()->create(['password' => 'password']);
        $this->category = Category::firstOrCreate(['name' => 'Zz Trending Test Category']);
    }

    private function makePost(string $title, int $views, int $daysOld, string $status = 'published'): Post
    {
        $post = Post::create([
            'admin_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => $title,
            'excerpt' => 'Excerpt',
            'content' => '<p>Body</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => $status,
            'views' => $views,
        ]);
        $post->forceFill(['created_at' => now()->subDays($daysOld)])->save();

        return $post;
    }

    private function pick(Post $post): Post
    {
        $post->forceFill(['trending_until' => now()->addDays(Post::PINNED_TRENDING_DAYS)])->save();

        return $post;
    }

    private function addLikes(Post $post, int $count): void
    {
        foreach (range(1, $count) as $i) {
            $post->likedByUsers()->attach(User::factory()->create(['password' => 'password'])->id);
        }
    }

    public function test_popular_posts_do_not_trend_unless_an_admin_picks_them(): void
    {
        $popular = $this->makePost('Zz Most Viewed', 5000, 1);
        $this->addLikes($popular, 3);
        Comment::create(['post_id' => $popular->id, 'user_id' => $this->admin->id, 'content' => 'Nice', 'approved_at' => now()]);

        $this->assertCount(0, Post::trendingNow(8));

        $this->pick($popular);

        $this->assertSame(['Zz Most Viewed'], Post::trendingNow(8)->pluck('title')->all());
    }

    public function test_picked_posts_carry_their_stats(): void
    {
        $post = $this->pick($this->makePost('Zz Picked Story', 4, 1));
        $this->addLikes($post, 2);
        Comment::create(['post_id' => $post->id, 'user_id' => $this->admin->id, 'content' => 'Nice', 'approved_at' => now()]);

        $trending = Post::trendingNow(8)->first();

        $this->assertSame(2, (int) $trending->trend_likes);
        $this->assertSame(1, (int) $trending->trend_comments);
        $this->assertSame(4 + 2 * 2 + 3, $trending->trend_score);
    }

    public function test_picked_drafts_never_trend(): void
    {
        $this->pick($this->makePost('Zz Draft Hit', 100, 1, 'draft'));

        $this->assertNotContains('Zz Draft Hit', Post::trendingNow(8)->pluck('title'));
    }

    public function test_homepage_hides_trending_until_a_post_is_picked(): void
    {
        $post = $this->makePost('Zz Homepage Story', 900, 1);

        $this->get(url('/'))->assertOk()->assertViewHas('trendingPosts', fn ($posts) => $posts->isEmpty());

        $this->pick($post);

        $this->get(url('/'))
            ->assertOk()
            ->assertSee('Trending')
            ->assertSee('Zz Homepage Story');
    }

    public function test_admin_dashboard_lists_only_picked_posts(): void
    {
        $this->admin->forceFill(['role_as' => 1])->save();
        $this->makePost('Zz Old Views Giant', 5000, 90);
        $this->pick($this->makePost('Zz Picked Now', 2, 1));

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Trending now')
            ->assertDontSee('Most viewed')
            ->assertSee('Zz Picked Now')
            ->assertDontSee('Zz Old Views Giant')
            ->assertViewHas('trendingPosts', fn ($posts) => $posts->pluck('title')->all() === ['Zz Picked Now']);
    }

    public function test_category_sidebar_shows_picked_posts_even_with_no_views(): void
    {
        $picked = $this->pick($this->makePost('Zz Picked No Views', 0, 1));

        $this->get(route('category.page', $this->category->slug))
            ->assertOk()
            ->assertViewHas('trendingPosts', fn ($posts) => $posts->pluck('id')->all() === [$picked->id])
            ->assertSee('Zz Picked No Views');
    }
}
