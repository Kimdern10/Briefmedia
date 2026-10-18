<?php

namespace Tests\Feature;

use App\Livewire\Admin\CommentsTable;
use App\Livewire\Admin\NewsletterSender;
use App\Livewire\PostComments;
use App\Mail\NewsletterMail;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PublishingFeaturesTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Storage::fake('public');
        Cache::flush();

        $this->admin = User::factory()->create(['password' => 'password', 'name' => 'Desk Editor']);
        $this->admin->forceFill(['role_as' => 1])->save();
        $this->category = Category::firstOrCreate(['name' => 'Zz Publishing Features Category']);
    }

    private function makePost(array $attributes = []): Post
    {
        return Post::create(array_merge([
            'admin_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'Zz Feature Story '.uniqid(),
            'excerpt' => 'Excerpt',
            'content' => '<p>Body</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => 'published',
        ], $attributes));
    }

    /* ---------- 1. Scheduled posts ---------- */

    public function test_scheduled_post_stays_hidden_then_publishes_itself(): void
    {
        $goLive = now()->addHours(3)->startOfMinute();

        $this->actingAs($this->admin)->post(route('admin.posts.store'), [
            'category_id' => $this->category->id,
            'title' => 'Zz Scheduled Budget Story',
            'content' => '<p>Body</p>',
            'status' => 'scheduled',
            'publish_at' => $goLive->format('Y-m-d\TH:i'),
            'featured_image_1' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertSessionHasNoErrors();

        $post = Post::where('title', 'Zz Scheduled Budget Story')->firstOrFail();
        $this->assertSame('draft', $post->status);
        $this->assertTrue($post->isScheduled());
        $this->assertTrue($post->publish_at->equalTo($goLive));

        $this->assertSame(0, Post::publishDueScheduled());
        $this->get(route('user.posts.search', ['query' => 'Zz Scheduled Budget']))->assertDontSee('Zz Scheduled Budget Story');

        $this->travelTo($goLive->copy()->addMinute());

        $this->artisan('posts:publish-scheduled')->expectsOutputToContain('Published 1')->assertSuccessful();

        $post->refresh();
        $this->assertSame('published', $post->status);
        $this->assertNull($post->publish_at);
        $this->assertTrue($post->created_at->equalTo($goLive));
    }

    public function test_scheduled_posts_also_publish_when_someone_visits_the_site(): void
    {
        $post = $this->makePost(['status' => 'draft', 'publish_at' => now()->subMinute()]);

        $this->get(url('/'))->assertOk();

        $this->assertSame('published', $post->fresh()->status);
    }

    public function test_schedule_time_must_be_in_the_future(): void
    {
        $this->actingAs($this->admin)->post(route('admin.posts.store'), [
            'category_id' => $this->category->id,
            'title' => 'Zz Past Schedule',
            'content' => '<p>Body</p>',
            'status' => 'scheduled',
            'publish_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'featured_image_1' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertSessionHasErrors('publish_at');
    }

    /* ---------- 2. Sitemaps ---------- */

    public function test_sitemap_lists_published_posts_and_categories_only(): void
    {
        $published = $this->makePost(['title' => 'Zz Sitemap Published']);
        $draft = $this->makePost(['title' => 'Zz Sitemap Draft', 'status' => 'draft']);

        $response = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringStartsWith('application/xml', $response->headers->get('Content-Type'));
        $response->assertSee(route('posts.show', $published->slug), false)
            ->assertSee(route('category.page', $this->category->slug), false)
            ->assertDontSee(route('posts.show', $draft->slug), false);

        $this->assertNotFalse(simplexml_load_string($response->getContent()), 'sitemap must be valid XML');
    }

    public function test_news_sitemap_only_has_the_last_two_days(): void
    {
        $fresh = $this->makePost(['title' => 'Zz News Fresh']);
        $old = $this->makePost(['title' => 'Zz News Old']);
        $old->forceFill(['created_at' => now()->subDays(5)])->save();

        $response = $this->get('/news-sitemap.xml')->assertOk()
            ->assertSee('<news:title>Zz News Fresh</news:title>', false)
            ->assertDontSee('Zz News Old');

        $this->assertNotFalse(simplexml_load_string($response->getContent()));
    }

    public function test_robots_txt_points_to_both_sitemaps(): void
    {
        $this->get('/robots.txt')->assertOk()
            ->assertSee('Sitemap: '.url('/sitemap.xml'))
            ->assertSee('Sitemap: '.url('/news-sitemap.xml'))
            ->assertSee('Disallow: /admin');
    }

    /* ---------- 3. Photo caption and credit ---------- */

    public function test_caption_and_credit_show_under_the_main_photo(): void
    {
        $post = $this->makePost([
            'image_caption' => 'Governor Soludo at Eke Awka market',
            'image_credit' => 'BriefMedia / Chioma',
        ]);

        $this->get(route('posts.show', $post))->assertOk()
            ->assertSee('Governor Soludo at Eke Awka market')
            ->assertSee('Photo: BriefMedia / Chioma');
    }

    /* ---------- 4. Send newsletter button ---------- */

    public function test_send_newsletter_button_really_sends(): void
    {
        Mail::fake();
        $this->makePost(['title' => 'Zz Newsletter Lead']);
        Subscriber::create(['email' => 'zz-reader@example.com', 'is_active' => true, 'unsubscribe_token' => 'zz-token']);

        Livewire::actingAs($this->admin)
            ->test(NewsletterSender::class)
            ->call('send')
            ->assertDispatched('admin-toast')
            ->assertSee('Last sent');

        Mail::assertSent(NewsletterMail::class, fn (NewsletterMail $mail) => $mail->hasTo('zz-reader@example.com'));
    }

    public function test_readers_cannot_trigger_the_newsletter(): void
    {
        Livewire::actingAs(User::factory()->create(['password' => 'password']))
            ->test(NewsletterSender::class)
            ->assertForbidden();
    }

    /* ---------- 5. Comment approval ---------- */

    public function test_reader_comments_wait_for_approval(): void
    {
        $post = $this->makePost();
        $reader = User::factory()->create(['password' => 'password', 'name' => 'Ada Reader']);

        Livewire::actingAs($reader)
            ->test(PostComments::class, ['post' => $post])
            ->set('content', 'Zz waiting comment')
            ->call('postComment')
            ->assertSee('once it is approved')
            ->assertSee('Zz waiting comment')
            ->assertSee('Awaiting approval');

        $comment = Comment::where('content', 'Zz waiting comment')->firstOrFail();
        $this->assertNull($comment->approved_at);

        // Other visitors don't see it yet, and it doesn't count
        auth()->logout();
        $this->get(route('posts.show', $post))->assertOk()->assertDontSee('Zz waiting comment');
        $this->assertSame(0, $post->fresh()->comments()->count());

        Livewire::actingAs($this->admin)
            ->test(CommentsTable::class)
            ->assertSee('Zz waiting comment')
            ->call('approve', $comment->id);

        $this->get(route('posts.show', $post))->assertOk()->assertSee('Zz waiting comment');
    }

    public function test_admin_comments_appear_straight_away(): void
    {
        $post = $this->makePost();

        Livewire::actingAs($this->admin)
            ->test(PostComments::class, ['post' => $post])
            ->set('content', 'Zz admin comment')
            ->call('postComment')
            ->assertDontSee('Awaiting approval');

        $this->assertNotNull(Comment::where('content', 'Zz admin comment')->value('approved_at'));
    }

    /* ---------- 6. Writer pages ---------- */

    public function test_writer_page_lists_stories_by_typed_in_writer_name(): void
    {
        $byChioma = $this->makePost(['title' => 'Zz Chioma Story', 'author_name' => 'Chioma Agbomma']);
        $this->makePost(['title' => 'Zz Someone Else Story', 'author_name' => 'Samuel Uzo']);
        $this->makePost(['title' => 'Zz Chioma Draft', 'author_name' => 'Chioma Agbomma', 'status' => 'draft']);

        $this->assertSame(url('/writer/chioma-agbomma'), $byChioma->authorUrl());

        // The header ticker lists every latest story, so check the writer's post list itself
        $this->get('/writer/chioma-agbomma')->assertOk()
            ->assertSee('Chioma Agbomma')
            ->assertViewHas('posts', fn ($posts) => $posts->pluck('title')->all() === ['Zz Chioma Story']);
    }

    public function test_writer_page_falls_back_to_the_posting_admin(): void
    {
        $this->makePost(['title' => 'Zz Editor Story']);

        $this->get('/writer/desk-editor')->assertOk()->assertSee('Zz Editor Story');
        $this->get('/writer/nobody-by-this-name')->assertNotFound();
    }

    public function test_posted_by_links_to_the_writer_page(): void
    {
        $post = $this->makePost(['author_name' => 'Chioma Agbomma']);

        $this->get(route('posts.show', $post))->assertOk()->assertSee(url('/writer/chioma-agbomma'), false);
    }

    /* ---------- 7. Most read today ---------- */

    public function test_views_are_counted_per_day_and_ranked(): void
    {
        DB::table('post_daily_views')->delete();

        $popular = $this->makePost(['title' => 'Zz Popular Today']);
        $quieter = $this->makePost(['title' => 'Zz Quieter Today']);
        $third = $this->makePost(['title' => 'Zz Third Today']);

        foreach (range(1, 3) as $i) {
            Post::recordDailyView($popular->id);
        }
        Post::recordDailyView($quieter->id);
        Post::recordDailyView($third->id);

        $mostRead = Post::mostReadToday(5);
        $this->assertSame('Zz Popular Today', $mostRead->first()->title);
        $this->assertSame(3, $mostRead->first()->views_today);

        $this->get(url('/'))->assertOk()->assertSee('Most read today')->assertSee('Zz Popular Today');
    }

    public function test_opening_a_story_counts_a_view_for_today(): void
    {
        DB::table('post_daily_views')->delete();
        $post = $this->makePost();

        $this->get(route('posts.show', $post))->assertOk();

        $this->assertSame(1, (int) DB::table('post_daily_views')
            ->where('post_id', $post->id)->where('date', now()->toDateString())->value('views'));
    }

    /* ---------- Longer summaries ---------- */

    public function test_summary_can_be_up_to_2000_characters_on_new_and_edited_posts(): void
    {
        $long = str_repeat('A longer summary of the story. ', 60); // ~1,800 characters

        $this->actingAs($this->admin)->post(route('admin.posts.store'), [
            'category_id' => $this->category->id,
            'title' => 'Zz Long Summary Story',
            'excerpt' => $long,
            'content' => '<p>Body</p>',
            'status' => 'published',
            'featured_image_1' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertSessionHasNoErrors();

        $post = Post::where('title', 'Zz Long Summary Story')->firstOrFail();
        $this->assertSame(trim($long), $post->excerpt);

        $this->actingAs($this->admin)->put(route('admin.posts.update', $post->id), [
            'category_id' => $this->category->id,
            'title' => 'Zz Long Summary Story',
            'excerpt' => $long.$long,
            'content' => '<p>Body</p>',
            'status' => 'published',
        ])->assertSessionHasErrors('excerpt');

        $this->actingAs($this->admin)->get(route('admin.posts.edit', $post->id))->assertOk()
            ->assertSee('maxlength="2000"', false);
    }
}
