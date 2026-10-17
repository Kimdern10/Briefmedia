<?php

namespace Tests\Feature;

use App\Livewire\Admin\PostsTable;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class PinnedTrendingTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('posts')->update(['views' => 0, 'trending_until' => null]);

        $this->admin = User::factory()->create(['password' => 'password']);
        $this->admin->forceFill(['role_as' => 1])->save();
        $this->category = Category::firstOrCreate(['name' => 'Zz Pinned Trending Category']);
    }

    private function makePost(string $title, int $views = 0, string $status = 'published', int $daysOld = 1): Post
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

    public function test_trend_button_pins_a_post_for_two_days(): void
    {
        $post = $this->makePost('Zz Pin Me');

        $this->travelTo(now()->startOfMinute());

        Livewire::actingAs($this->admin)
            ->test(PostsTable::class, ['mode' => 'active'])
            ->call('toggleTrending', $post->id)
            ->assertDispatched('admin-toast');

        $post->refresh();
        $this->assertTrue($post->isPinnedTrending());
        $this->assertTrue($post->trending_until->equalTo(now()->addDays(2)));
    }

    public function test_only_picked_posts_trend_newest_pick_first(): void
    {
        $this->makePost('Zz Popular Story', 500);
        $this->makePost('Zz Second Story', 300);
        $quiet = $this->makePost('Zz Quiet But Picked', 0, 'published', 20);
        $quiet->forceFill(['trending_until' => now()->addDays(2)])->save();
        $earlier = $this->makePost('Zz Picked Yesterday', 10);
        $earlier->forceFill(['trending_until' => now()->addDay()])->save();

        $trending = Post::trendingNow(8);

        $this->assertSame(['Zz Quiet But Picked', 'Zz Picked Yesterday'], $trending->pluck('title')->all());
        $this->assertTrue($trending->first()->trend_pinned);
    }

    public function test_pick_expires_after_two_days(): void
    {
        $post = $this->makePost('Zz Expiring Pick');
        $post->forceFill(['trending_until' => now()->addDays(2)])->save();

        $this->assertContains('Zz Expiring Pick', Post::trendingNow(8)->pluck('title'));

        $this->travel(2)->days();
        $this->travel(1)->minutes();

        $this->assertFalse($post->fresh()->isPinnedTrending());
        $this->assertNotContains('Zz Expiring Pick', Post::trendingNow(8)->pluck('title'));
    }

    public function test_clicking_again_removes_the_pick(): void
    {
        $post = $this->makePost('Zz Unpin Me');
        $post->forceFill(['trending_until' => now()->addDays(2)])->save();

        Livewire::actingAs($this->admin)
            ->test(PostsTable::class, ['mode' => 'active'])
            ->call('toggleTrending', $post->id);

        $this->assertNull($post->fresh()->trending_until);
    }

    public function test_drafts_cannot_be_picked_and_unpublishing_removes_the_pick(): void
    {
        $draft = $this->makePost('Zz Draft Pick', 0, 'draft');

        Livewire::actingAs($this->admin)
            ->test(PostsTable::class, ['mode' => 'active'])
            ->call('toggleTrending', $draft->id)
            ->assertDispatched('admin-toast', tone: 'warning');
        $this->assertNull($draft->fresh()->trending_until);

        $published = $this->makePost('Zz Published Pick');
        $published->forceFill(['trending_until' => now()->addDays(2)])->save();

        Livewire::actingAs($this->admin)
            ->test(PostsTable::class, ['mode' => 'active'])
            ->call('toggleStatus', $published->id);

        $this->assertSame('draft', $published->fresh()->status);
        $this->assertNull($published->fresh()->trending_until);
    }

    public function test_trending_tab_lists_only_picked_posts(): void
    {
        $picked = $this->makePost('Zz Tab Picked');
        $picked->forceFill(['trending_until' => now()->addDays(2)])->save();
        $this->makePost('Zz Tab Not Picked');

        Livewire::actingAs($this->admin)
            ->test(PostsTable::class, ['mode' => 'active'])
            ->set('search', 'Zz Tab')
            ->call('setStatus', 'trending')
            ->assertSee('Zz Tab Picked')
            ->assertSee('left')
            ->assertDontSee('Zz Tab Not Picked');
    }

    public function test_homepage_shows_the_featured_badge(): void
    {
        $post = $this->makePost('Zz Homepage Featured Pick');
        $post->forceFill(['trending_until' => now()->addDays(2)])->save();

        $this->get(url('/'))
            ->assertOk()
            ->assertSee('Zz Homepage Featured Pick')
            ->assertSee('🔥 Featured');
    }
}
