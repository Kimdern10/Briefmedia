<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Everyone sees the news first: guests can open every public page, and signing in, signing up
 * or the old /home address lead to the homepage instead of a login page or dashboard.
 */
class NewsFirstTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    private function reader(array $attributes = []): User
    {
        $reader = User::factory()->create(['password' => 'password']);
        $reader->forceFill(array_merge(['role_as' => '0', 'active' => 1], $attributes))->save();

        return $reader;
    }

    public function test_guests_can_open_every_public_page(): void
    {
        $admin = User::factory()->create(['password' => 'password']);
        $category = Category::firstOrCreate(['name' => 'Zz News First']);
        $post = Post::create([
            'admin_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Zz Open To Everyone',
            'content' => '<p>Body</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => 'published',
        ]);

        foreach ([
            url('/'),
            route('category.page', $category),
            route('posts.show', $post),
            route('user.posts.search', ['query' => 'Zz']),
            route('contact'),
            route('privacy'),
            route('terms'),
        ] as $page) {
            $this->get($page)->assertOk();
        }
    }

    public function test_the_old_home_address_shows_the_news_to_guests_and_readers(): void
    {
        $this->get('/home')->assertRedirect(route('home'));

        $this->actingAs($this->reader())->get('/home')->assertRedirect(route('home'));
    }

    public function test_the_old_reader_dashboard_address_shows_the_news_instead_of_the_login_page(): void
    {
        // Browsers suggest this address from history, so guests must not land on the login form
        $this->get('/user/dashboard')->assertRedirect(route('home'));

        $this->actingAs($this->reader())->get('/user/dashboard')->assertRedirect(route('home'));
    }

    public function test_readers_land_on_the_news_after_signing_in(): void
    {
        $reader = $this->reader();

        $this->post(route('login'), ['email' => $reader->email, 'password' => 'password'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($reader);

        // Already signed in: the login page sends them to the news too
        $this->get(route('login'))->assertRedirect(url('/'));
    }

    public function test_verifying_the_email_code_signs_the_reader_in_and_shows_the_news(): void
    {
        $reader = $this->reader([
            'email_verified_at' => null,
            'email_verification_otp' => '123456',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->post(route('verify.otp.submit', ['email' => $reader->email]), ['otp' => '123456'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($reader);
        $this->assertNotNull($reader->fresh()->email_verified_at);
    }
}
