<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheGuestHomepageTest extends TestCase
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

        $this->get(url('/?utm_source=facebook'))->assertOk()->assertHeaderMissing('X-BM-Page-Cache');
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
