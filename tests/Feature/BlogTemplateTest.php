<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Smoke tests for the redesigned public site and admin panel.
 * Everything runs inside a transaction, so the local database is left untouched.
 */
class BlogTemplateTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Category $category;

    private Post $publishedPost;

    private Post $draftPost;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['password' => 'password']);
        $this->admin->forceFill(['role_as' => 1])->save();

        $this->category = Category::create([
            'name' => 'Zz Template Test Category',
            'description' => 'Stories used by the template tests.',
        ]);

        $this->publishedPost = $this->makePost('Zebra Published Headline For Template Test', 'published');
        $this->draftPost = $this->makePost('Zebra Draft Headline For Template Test', 'draft');
    }

    private function makePost(string $title, string $status): Post
    {
        return Post::create([
            'admin_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => $title,
            'excerpt' => 'A short excerpt for '.$title,
            'content' => '<p>Body copy for '.$title.'</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => $status,
            'views' => 3,
            'enable_auto_seo' => true,
        ]);
    }

    public function test_homepage_renders_the_new_template_with_published_posts_only(): void
    {
        $response = $this->get(url('/'));

        $response->assertOk()
            ->assertSee('bm-hero', false)
            ->assertSee('Zz Template Test Category')
            ->assertSee($this->publishedPost->title)
            ->assertDontSee($this->draftPost->title)
            ->assertSee(route('newsletter.subscribe'), false);
    }

    public function test_post_page_shows_article_comments_and_share_links(): void
    {
        $commenter = User::factory()->create(['password' => 'password']);
        Comment::create([
            'post_id' => $this->publishedPost->id,
            'user_id' => $commenter->id,
            'content' => 'Great read, thanks!',
        ]);

        $this->get(route('posts.show', $this->publishedPost))
            ->assertOk()
            ->assertSee('bm-article__title', false)
            ->assertSee($this->publishedPost->title)
            ->assertSee('Great read, thanks!')
            ->assertSee('wa.me/?text=', false)
            ->assertDontSee($this->draftPost->title);
    }

    public function test_logged_in_reader_gets_like_button_and_comment_form(): void
    {
        $reader = User::factory()->create(['password' => 'password']);

        $this->actingAs($reader)
            ->get(route('posts.show', $this->publishedPost))
            ->assertOk()
            ->assertSee(route('posts.like', $this->publishedPost), false)
            ->assertSee(route('posts.comment', $this->publishedPost), false);
    }

    public function test_category_page_lists_its_published_posts(): void
    {
        $this->get(route('category.page', $this->category->slug))
            ->assertOk()
            ->assertSee('bm-page-hero__title', false)
            ->assertSee($this->publishedPost->title)
            ->assertDontSee($this->draftPost->title);
    }

    public function test_search_only_returns_published_posts(): void
    {
        $this->get(route('user.posts.search', ['query' => 'Zebra']))
            ->assertOk()
            ->assertSee($this->publishedPost->title)
            ->assertDontSee($this->draftPost->title);
    }

    public function test_auth_and_static_pages_render(): void
    {
        foreach (['login', 'sign-up', 'forgetPassword', 'contact', 'terms', 'privacy'] as $routeName) {
            $this->get(route($routeName))->assertOk()->assertSee('bm-logo', false);
        }
    }

    public function test_privacy_policy_page_renders_and_is_linked_from_every_page(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('Google AdSense');

        $this->get(url('/'))->assertOk()->assertSee(route('privacy'), false);
        $this->get(route('sign-up'))->assertOk()->assertSee(route('privacy'), false);
    }

    public function test_profile_page_renders_for_a_reader(): void
    {
        $reader = User::factory()->create(['password' => 'password']);

        $this->actingAs($reader)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('bm-account', false)
            ->assertSee(route('password-update'), false);
    }

    public function test_admin_pages_render_with_the_new_admin_layout(): void
    {
        $pages = [
            route('admin.dashboard'),
            route('admin.posts.index'),
            route('admin.posts.create'),
            route('admin.posts.edit', $this->publishedPost->id),
            route('admin.posts.trash'),
            route('admin.categories.index'),
            route('admin.categories.create'),
            route('admin.categories.edit', $this->category->id),
            route('admin.categories.trash'),
            route('admin.comments'),
            route('admin.subscribers'),
            route('user.list'),
            route('users.trashed'),
            route('admin.seo.edit'),
            route('admin.news.headlines'),
        ];

        foreach ($pages as $url) {
            $this->actingAs($this->admin)
                ->get($url)
                ->assertOk()
                ->assertSee('ad-side', false);
        }
    }

    public function test_site_and_admin_pages_include_a_tab_icon(): void
    {
        $this->get(url('/'))->assertOk()->assertSee('rel="icon"', false);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('rel="icon"', false);
    }

    public function test_regular_users_cannot_open_the_admin_area(): void
    {
        $reader = User::factory()->create(['password' => 'password']);

        $this->actingAs($reader)
            ->get(route('admin.dashboard'))
            ->assertRedirect(url('/'))
            ->assertSessionHas('error');

        $this->actingAs($reader)
            ->delete(route('admin.posts.destroy', $this->publishedPost->id))
            ->assertRedirect(url('/'));

        $this->assertNotSoftDeleted($this->publishedPost);

        $this->actingAs($reader)
            ->getJson(route('admin.posts.index'))
            ->assertForbidden();
    }

    public function test_guests_are_sent_to_login_from_the_admin_area(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_post_editor_keeps_every_field_the_controller_expects(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.posts.edit', $this->publishedPost->id));

        foreach ([
            'category_id', 'title', 'excerpt', 'content', 'status', 'enable_auto_seo',
            'featured_image_1', 'featured_image_2', 'featured_image_3', 'featured_image_4',
            'meta_title', 'meta_description', 'meta_keywords', 'meta_image', 'canonical_url',
            'og_title', 'og_description', 'og_image', 'twitter_title', 'twitter_description',
            'twitter_image', 'schema_markup', 'no_index', 'no_follow', 'remove_featured_image_1',
        ] as $field) {
            $response->assertSee('name="'.$field.'"', false);
        }
    }
}
