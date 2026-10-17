<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

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

    private function addLikes(Post $post, int $count): void
    {
        foreach (range(1, $count) as $i) {
            $post->likedByUsers()->attach(User::factory()->create(['password' => 'password'])->id);
        }
    }

    public function test_likes_and_comments_count_towards_the_score(): void
    {
        $mostViewed = $this->makePost('Zz Most Viewed', 10, 1);
        $mostLoved = $this->makePost('Zz Most Loved', 4, 1);
        $this->addLikes($mostLoved, 2);                                   // 4 + 2x2 = 8
        Comment::create(['post_id' => $mostLoved->id, 'user_id' => $this->admin->id, 'content' => 'Nice', 'approved_at' => now()]); // + 3 = 11
        $this->makePost('Zz Quiet One', 1, 1);
        $this->makePost('Zz Fourth', 2, 1);

        $trending = Post::trendingNow(8);

        $this->assertSame(['Zz Most Loved', 'Zz Most Viewed', 'Zz Fourth', 'Zz Quiet One'], $trending->pluck('title')->all());
        $this->assertSame(2, (int) $trending->first()->trend_likes);
        $this->assertSame(1, (int) $trending->first()->trend_comments);
        $this->assertSame(11, $trending->first()->trend_score);
    }

    public function test_only_the_last_7_days_count_when_there_are_enough_stories(): void
    {
        foreach (range(1, 4) as $i) {
            $this->makePost("Zz Fresh {$i}", $i, 2);
        }
        $this->makePost('Zz Old But Popular', 999, 20);

        $titles = Post::trendingNow(8)->pluck('title');

        $this->assertNotContains('Zz Old But Popular', $titles);
        $this->assertCount(4, $titles);
    }

    public function test_window_widens_to_30_days_when_the_week_is_quiet(): void
    {
        $this->makePost('Zz This Week', 5, 1);
        $this->makePost('Zz Two Weeks Ago', 50, 14);
        $this->makePost('Zz Too Old', 500, 45);

        $titles = Post::trendingNow(8)->pluck('title')->all();

        $this->assertSame(['Zz Two Weeks Ago', 'Zz This Week'], $titles);
    }

    public function test_drafts_and_posts_without_activity_never_trend(): void
    {
        $this->makePost('Zz Draft Hit', 100, 1, 'draft');
        $this->makePost('Zz No Activity', 0, 1);

        $titles = Post::trendingNow(8)->pluck('title');

        $this->assertNotContains('Zz Draft Hit', $titles);
        $this->assertNotContains('Zz No Activity', $titles);
    }

    public function test_homepage_shows_trending_with_real_like_counts(): void
    {
        $post = $this->makePost('Zz Homepage Trending Story', 3, 1);
        $this->addLikes($post, 2);

        $this->get(url('/'))
            ->assertOk()
            ->assertSee('Trending')
            ->assertSee('Zz Homepage Trending Story');
    }

    public function test_admin_dashboard_uses_the_trending_rule_not_all_time_views(): void
    {
        $this->admin->forceFill(['role_as' => 1])->save();
        $this->makePost('Zz Old Views Giant', 5000, 90);
        $this->makePost('Zz Old Views Draft', 9000, 1, 'draft');
        $talkedAbout = $this->makePost('Zz Talked About Now', 2, 1);
        $this->addLikes($talkedAbout, 2);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Trending now')
            ->assertDontSee('Most viewed')
            ->assertSee('Zz Talked About Now')
            ->assertDontSee('Zz Old Views Giant')
            // The draft is still listed under "Latest posts", so check the trending list itself
            ->assertViewHas('trendingPosts', fn ($posts) => $posts->pluck('title')->all() === ['Zz Talked About Now']);
    }

    public function test_category_sidebar_keeps_trending_stories_with_no_views(): void
    {
        $liked = $this->makePost('Zz Liked No Views', 0, 1);
        $this->addLikes($liked, 1);

        $this->get(route('category.page', $this->category->slug))
            ->assertOk()
            ->assertViewHas('trendingPosts', fn ($posts) => $posts->contains('id', $liked->id))
            ->assertSee('Zz Liked No Views');
    }
}
