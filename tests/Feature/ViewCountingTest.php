<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Services\SeenStories;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * A guest's view of a story counts only once, remembered in one small cookie.
 */
class ViewCountingTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->admin = User::factory()->create(['password' => 'password']);
    }

    private function makePost(string $title): Post
    {
        return Post::create([
            'admin_id' => $this->admin->id,
            'category_id' => Category::firstOrCreate(['name' => 'Zz View Counting'])->id,
            'title' => $title,
            'content' => '<p>Body</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => 'published',
        ]);
    }

    private function seenCookieFrom(TestResponse $response): string
    {
        return $response->getCookie(SeenStories::COOKIE, false)->getValue();
    }

    public function test_a_guest_counts_once_per_story_with_one_cookie_for_all_stories(): void
    {
        $first = $this->makePost('Zz First Story');
        $second = $this->makePost('Zz Second Story');

        $seen = $this->seenCookieFrom($this->get(route('posts.show', $first))->assertOk());
        $seen = $this->seenCookieFrom($this->withUnencryptedCookie(SeenStories::COOKIE, $seen)->get(route('posts.show', $second)));

        // Both stories in one small cookie, and no per-story cookie any more
        $this->assertCount(2, explode('-', $seen));
        $this->assertLessThan(40, strlen($seen));

        $again = $this->withUnencryptedCookie(SeenStories::COOKIE, $seen)->get(route('posts.show', $first))->assertOk();
        $this->assertNull($again->getCookie('viewed_post_'.$first->id, false));

        $this->assertSame(1, (int) $first->fresh()->views);
        $this->assertSame(1, (int) $second->fresh()->views);
    }

    public function test_a_returning_guest_is_never_counted_again_and_the_cookie_is_renewed(): void
    {
        $post = $this->makePost('Zz Years Later Story');

        $seen = $this->seenCookieFrom($this->get(route('posts.show', $post)));

        foreach ([1, 300, 3650] as $daysLater) {
            $this->travel($daysLater)->days();
            $again = $this->withUnencryptedCookie(SeenStories::COOKIE, $seen)->get(route('posts.show', $post))->assertOk();

            // The browser is told to keep the cookie for another 400 days from this visit
            $cookie = $again->getCookie(SeenStories::COOKIE, false);
            $this->assertSame($seen, $cookie->getValue());
            $this->assertEqualsWithDelta(now()->addDays(SeenStories::KEEP_DAYS)->getTimestamp(), $cookie->getExpiresTime(), 120);
        }

        $this->assertSame(1, (int) $post->fresh()->views);
    }

    public function test_readers_with_the_old_cookies_are_not_counted_twice_and_the_old_cookies_are_removed(): void
    {
        $readBefore = $this->makePost('Zz Read Before Story');
        $other = $this->makePost('Zz Other Story');

        $response = $this->withCookie('viewed_post_'.$readBefore->id, '1')->get(route('posts.show', $readBefore))->assertOk();

        $this->assertSame(0, (int) $readBefore->fresh()->views);
        $this->assertTrue($response->getCookie('viewed_post_'.$readBefore->id, false)->isCleared());

        // The old view is carried over into the new cookie
        $seen = $this->seenCookieFrom($response);
        $this->withUnencryptedCookie(SeenStories::COOKIE, $seen)->get(route('posts.show', $readBefore));
        $this->assertSame(0, (int) $readBefore->fresh()->views);

        $this->withUnencryptedCookie(SeenStories::COOKIE, $seen)->get(route('posts.show', $other));
        $this->assertSame(1, (int) $other->fresh()->views);
    }

    public function test_admin_and_staff_visits_are_not_counted_but_readers_are(): void
    {
        $post = $this->makePost('Zz Staff Visit Story');

        foreach (['1', '2', '3'] as $role) {
            $staff = User::factory()->create(['password' => 'password']);
            $staff->forceFill(['role_as' => $role])->save();

            $this->actingAs($staff)->get(route('posts.show', $post))->assertOk();
        }

        $this->assertSame(0, (int) $post->fresh()->views);
        $this->assertDatabaseMissing('post_daily_views', ['post_id' => $post->id]);

        $reader = User::factory()->create(['password' => 'password']);
        $reader->forceFill(['role_as' => '0'])->save();
        $this->actingAs($reader)->get(route('posts.show', $post))->assertOk();

        $this->assertSame(1, (int) $post->fresh()->views);
    }

    public function test_a_damaged_cookie_is_ignored(): void
    {
        $post = $this->makePost('Zz Damaged Cookie Story');

        $this->withUnencryptedCookie(SeenStories::COOKIE, 'not a real value!!')->get(route('posts.show', $post))->assertOk();

        $this->assertSame(1, (int) $post->fresh()->views);
    }
}
