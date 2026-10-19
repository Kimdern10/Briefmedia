<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheGuestPagesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    private function makePost(string $title): Post
    {
        $admin = User::factory()->create(['password' => 'password']);

        return Post::create([
            'admin_id' => $admin->id,
            'category_id' => Category::firstOrCreate(['name' => 'Zz Page Cache'])->id,
            'title' => $title,
            'content' => '<p>Body</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => 'published',
        ]);
    }

    public function test_guests_get_the_saved_homepage_with_their_own_security_token(): void
    {
        $this->get(url('/'))->assertOk()->assertHeader('X-BM-Page-Cache', 'miss');

        // A different visitor: new session, new token
        $this->flushSession();
        $second = $this->get(url('/'))->assertOk()->assertHeader('X-BM-Page-Cache', 'hit');
        $token = session()->token();

        $second->assertSee('<meta name="csrf-token" content="'.$token.'">', false)
            ->assertDontSee('__BM_CSRF_TOKEN__', false);
        $this->assertStringContainsString('text/html', $second->headers->get('Content-Type'));
    }

    public function test_the_lower_sections_are_saved_too_but_other_queries_are_not(): void
    {
        $this->get(url('/?part=more'))->assertOk()->assertHeader('X-BM-Page-Cache', 'miss');
        $this->get(url('/?part=more'))->assertOk()->assertHeader('X-BM-Page-Cache', 'hit');

        $this->get(url('/?anything=else'))->assertOk()->assertHeaderMissing('X-BM-Page-Cache');
    }

    public function test_links_shared_on_facebook_use_the_saved_page(): void
    {
        $this->get(url('/'))->assertOk()->assertHeader('X-BM-Page-Cache', 'miss');

        $this->get(url('/?fbclid=IwAR0abc&utm_source=facebook'))->assertOk()->assertHeader('X-BM-Page-Cache', 'hit');
    }

    public function test_category_pages_are_saved_per_page_number(): void
    {
        $post = $this->makePost('Zz Category Cache Story');
        $category = $post->category;

        $this->get(route('category.page', $category))->assertOk()->assertHeader('X-BM-Page-Cache', 'miss');
        $this->get(route('category.page', $category))->assertOk()->assertHeader('X-BM-Page-Cache', 'hit')->assertSee('Zz Category Cache Story');

        $this->get(route('category.page', [$category, 'page' => 2]))->assertOk()->assertHeader('X-BM-Page-Cache', 'miss');
        $this->get(route('category.page', [$category, 'page' => 'x']))->assertOk()->assertHeaderMissing('X-BM-Page-Cache');
    }

    public function test_story_pages_are_saved_and_still_count_each_new_reader(): void
    {
        $post = $this->makePost('Zz Story Cache');

        $this->get(route('posts.show', $post))->assertOk()->assertHeader('X-BM-Page-Cache', 'miss');
        $this->assertSame(1, (int) $post->fresh()->views);

        // A new reader (no "viewed" cookie) gets the saved page and is still counted
        $this->flushSession();
        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertHeader('X-BM-Page-Cache', 'hit')
            ->assertCookie('viewed_post_'.$post->id);
        $this->assertSame(2, (int) $post->fresh()->views);

        // The same reader again is not counted twice
        $this->withCookie('viewed_post_'.$post->id, '1')->get(route('posts.show', $post))->assertHeader('X-BM-Page-Cache', 'hit');
        $this->assertSame(2, (int) $post->fresh()->views);
    }

    public function test_a_new_comment_shows_on_the_saved_story_page_straight_away(): void
    {
        $post = $this->makePost('Zz Commented Story');
        $this->get(route('posts.show', $post))->assertOk();
        $this->get(route('posts.show', $post))->assertHeader('X-BM-Page-Cache', 'hit');

        Comment::create([
            'post_id' => $post->id,
            'user_id' => $post->admin_id,
            'content' => 'Zz A fresh approved comment',
            'approved_at' => now(),
        ]);

        $this->get(route('posts.show', $post))->assertOk()->assertHeader('X-BM-Page-Cache', 'miss')->assertSee('Zz A fresh approved comment');
    }

    public function test_a_new_story_shows_straight_away(): void
    {
        $this->get(url('/'))->assertOk();
        $this->get(url('/'))->assertHeader('X-BM-Page-Cache', 'hit');

        $this->makePost('Zz Just Published Story');

        $this->get(url('/'))->assertOk()->assertHeader('X-BM-Page-Cache', 'miss')->assertSee('Zz Just Published Story');
    }

    public function test_logged_in_readers_and_one_off_messages_skip_the_saved_page(): void
    {
        $this->get(url('/'))->assertOk();

        $reader = User::factory()->create(['password' => 'password']);
        $this->actingAs($reader)->get(url('/'))->assertOk()->assertHeaderMissing('X-BM-Page-Cache');

        auth()->logout();
        $this->withSession(['_flash.old' => ['success'], 'success' => 'Zz You are subscribed'])
            ->get(url('/'))
            ->assertOk()
            ->assertHeaderMissing('X-BM-Page-Cache')
            ->assertSee('Zz You are subscribed');
    }
}
