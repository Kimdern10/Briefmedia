<?php

namespace Tests\Feature;

use App\Livewire\Admin\AdSlotsManager;
use App\Livewire\Admin\LiveUpdatesManager;
use App\Livewire\BookmarkButton;
use App\Livewire\LiveUpdates;
use App\Livewire\PostPoll;
use App\Livewire\SavedPosts;
use App\Mail\NewsletterMail;
use App\Models\AdSlot;
use App\Models\Category;
use App\Models\Poll;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class NewsroomFeaturesTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private User $reader;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Storage::fake('public');
        Cache::flush();

        // Start from no tags and no ads, whatever the local database has (rolled back after each test)
        DB::table('post_tag')->delete();
        AdSlot::query()->delete();

        $this->admin = User::factory()->create(['password' => 'password', 'name' => 'Desk Editor']);
        $this->admin->forceFill(['role_as' => 1])->save();
        $this->reader = User::factory()->create(['password' => 'password']);
        $this->category = Category::firstOrCreate(['name' => 'Zz Newsroom Category']);
    }

    private function makePost(array $attributes = []): Post
    {
        return Post::create(array_merge([
            'admin_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'Zz Newsroom Story '.uniqid(),
            'excerpt' => 'Excerpt',
            'content' => '<p>One</p><p>Two</p><p>Three</p><p>Four</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => 'published',
        ], $attributes));
    }

    /**
     * @return array<string, mixed>
     */
    private function postForm(array $overrides = []): array
    {
        return array_merge([
            'category_id' => $this->category->id,
            'title' => 'Zz Form Story '.uniqid(),
            'content' => '<p>Body</p>',
            'status' => 'published',
            'featured_image_1' => UploadedFile::fake()->image('cover.jpg', 800, 500),
        ], $overrides);
    }

    /* ---------- Dashboard chart ---------- */

    public function test_dashboard_chart_shows_real_reads_for_the_last_7_days(): void
    {
        DB::table('post_daily_views')->delete();
        $post = $this->makePost();
        DB::table('post_daily_views')->insert([
            ['post_id' => $post->id, 'date' => now()->toDateString(), 'views' => 12],
            ['post_id' => $post->id, 'date' => now()->subDays(2)->toDateString(), 'views' => 5],
            ['post_id' => $post->id, 'date' => now()->subDays(30)->toDateString(), 'views' => 99],
        ]);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Reads, last 7 days')
            ->assertViewHas('viewsPerDay', function ($days) {
                return $days->count() === 7
                    && $days->last()->total === 12
                    && $days->get(4)->total === 5
                    && $days->sum('total') === 17;
            });
    }

    /* ---------- Tags ---------- */

    public function test_tags_are_saved_from_the_post_form_and_get_their_own_page(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $this->postForm(['title' => 'Zz Tagged Story', 'tags' => 'Soludo, ASUU, soludo ,  ']))
            ->assertSessionHasNoErrors();

        $post = Post::where('title', 'Zz Tagged Story')->firstOrFail();
        $this->assertSame(['ASUU', 'Soludo'], $post->tags()->pluck('name')->all());

        $this->makePost(['title' => 'Zz Tagged Draft', 'status' => 'draft'])->syncTags('Soludo');

        $this->get(route('posts.show', $post))->assertOk()->assertSee(route('tag.show', 'soludo'), false)->assertSee('#Soludo');

        $this->get('/tag/soludo')->assertOk()
            ->assertSee('Soludo')
            ->assertViewHas('posts', fn ($posts) => $posts->pluck('title')->all() === ['Zz Tagged Story'])
            ->assertSee('#ASUU');

        $this->get('/tag/nothing-here')->assertNotFound();
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('tag.show', 'soludo'), false);
    }

    public function test_editing_a_post_replaces_its_tags(): void
    {
        $post = $this->makePost();
        $post->syncTags('Old Topic');

        $this->actingAs($this->admin)
            ->put(route('admin.posts.update', $post->id), $this->postForm(['title' => $post->title, 'tags' => 'New Topic']))
            ->assertSessionHasNoErrors();

        $this->assertSame(['New Topic'], $post->fresh()->tags->pluck('name')->all());
    }

    /* ---------- Save for later ---------- */

    public function test_readers_can_save_stories_and_find_them_on_their_profile(): void
    {
        $post = $this->makePost(['title' => 'Zz Saved Story']);

        Livewire::actingAs($this->reader)
            ->test(BookmarkButton::class, ['post' => $post])
            ->assertSet('saved', false)
            ->call('toggle')
            ->assertSet('saved', true)
            ->assertDispatched('bookmarks-changed');

        $this->assertTrue($this->reader->bookmarks()->whereKey($post->id)->exists());

        $this->actingAs($this->reader)->get(route('profile'))->assertOk()->assertSee('Saved stories');

        Livewire::actingAs($this->reader)
            ->test(SavedPosts::class)
            ->assertSee('Zz Saved Story')
            ->call('remove', $post->id)
            ->assertDontSee('Zz Saved Story')
            ->assertSee('Nothing saved yet');
    }

    public function test_guests_are_sent_to_login_to_save(): void
    {
        Livewire::test(BookmarkButton::class, ['post' => $this->makePost()])
            ->call('toggle')
            ->assertRedirect(route('login'));
    }

    /* ---------- Live coverage ---------- */

    public function test_admin_runs_live_coverage_and_readers_see_updates(): void
    {
        $post = $this->makePost(['title' => 'Zz Election Live']);

        Livewire::actingAs($this->admin)
            ->test(LiveUpdatesManager::class, ['post' => $post])
            ->call('toggleLive')
            ->assertSet('isLive', true)
            ->set('title', 'Awka South declared')
            ->set('body', 'Results are in from all wards.')
            ->call('addUpdate')
            ->assertHasNoErrors()
            ->assertSee('Awka South declared');

        $this->assertTrue($post->fresh()->isLive());

        $this->get(route('posts.show', $post))->assertOk()
            ->assertSee('bm-chip--live', false)
            ->assertSee('Awka South declared')
            ->assertSee('"@type":"LiveBlogPosting"', false);

        Livewire::test(LiveUpdates::class, ['post' => $post])
            ->assertSee('Results are in from all wards.')
            ->assertSee('wire:poll.20s', false);

        // Ending coverage keeps the updates but stops refreshing
        Livewire::actingAs($this->admin)->test(LiveUpdatesManager::class, ['post' => $post->fresh()])->call('toggleLive');
        Livewire::test(LiveUpdates::class, ['post' => $post])
            ->assertSee('Coverage ended')
            ->assertDontSee('wire:poll.20s', false);
    }

    public function test_readers_cannot_use_the_live_coverage_panel(): void
    {
        Livewire::actingAs($this->reader)
            ->test(LiveUpdatesManager::class, ['post' => $this->makePost()])
            ->assertForbidden();
    }

    /* ---------- Polls ---------- */

    public function test_poll_from_the_post_form_takes_one_vote_per_reader(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $this->postForm([
                'title' => 'Zz Poll Story',
                'poll_question' => 'Should subsidy come back?',
                'poll_options' => "Yes\nNo\n\nNot sure",
            ]))
            ->assertSessionHasNoErrors();

        $poll = Post::where('title', 'Zz Poll Story')->firstOrFail()->poll;
        $this->assertSame(['Yes', 'No', 'Not sure'], $poll->options->pluck('label')->all());
        $yes = $poll->options->firstWhere('label', 'Yes');

        $this->get(route('posts.show', $poll->post))->assertOk()->assertSee('Should subsidy come back?');

        Livewire::actingAs($this->reader)
            ->test(PostPoll::class, ['poll' => $poll])
            ->set('choice', $yes->id)
            ->call('vote')
            ->assertSet('voted', true)
            ->assertSee('100%')
            ->assertSee('1 vote');

        // Voting again from the same account is ignored
        Livewire::actingAs($this->reader)
            ->test(PostPoll::class, ['poll' => $poll])
            ->assertSet('voted', true)
            ->set('choice', $poll->options->firstWhere('label', 'No')->id)
            ->call('vote');

        $this->assertSame(1, $poll->fresh()->totalVotes());
        $this->assertSame(1, $yes->fresh()->votes);
    }

    public function test_poll_needs_two_answers_and_rejects_foreign_options(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $this->postForm(['poll_question' => 'Only one?', 'poll_options' => 'Yes']))
            ->assertSessionHasErrors('poll_options');

        $post = $this->makePost();
        Poll::syncFor($post, 'Q?', ['A', 'B']);
        $otherPoll = Poll::syncFor($this->makePost(), 'Other?', ['C', 'D']) ?? Poll::latest('id')->first();

        $this->assertFalse($post->poll->vote($otherPoll->options->first()->id, 'user:1'));
        $this->assertSame(0, $post->poll->fresh()->totalVotes());
    }

    public function test_editing_poll_answers_keeps_votes_on_unchanged_answers(): void
    {
        $post = $this->makePost();
        Poll::syncFor($post, 'Best striker?', ['Osimhen', 'Lookman']);
        $poll = $post->poll()->first();
        $poll->vote($poll->options->first()->id, 'user:5');

        Poll::syncFor($post, 'Best striker?', ['Osimhen', 'Boniface']);

        $this->assertSame(['Osimhen' => 1, 'Boniface' => 0], $poll->fresh()->options->pluck('votes', 'label')->all());

        Poll::syncFor($post, '', []);
        $this->assertNull($post->poll()->first());
    }

    /* ---------- "Updated" label and correction note ---------- */

    public function test_editing_a_published_story_shows_updated_and_the_correction(): void
    {
        $post = $this->makePost(['title' => 'Zz Corrected Story']);
        $post->forceFill(['created_at' => now()->subHours(3)])->saveQuietly();

        $this->travel(1)->minutes();
        $this->actingAs($this->admin)
            ->put(route('admin.posts.update', $post->id), $this->postForm([
                'title' => 'Zz Corrected Story',
                'content' => '<p>Fixed the date.</p>',
                'correction_note' => 'An earlier version gave the wrong date.',
            ]))
            ->assertSessionHasNoErrors();

        $post->refresh();
        $this->assertTrue($post->wasUpdated());

        $this->get(route('posts.show', $post))->assertOk()
            ->assertSee('Updated')
            ->assertSee('An earlier version gave the wrong date.');
    }

    public function test_saving_without_changing_the_words_does_not_mark_it_updated(): void
    {
        $post = $this->makePost();
        $post->forceFill(['created_at' => now()->subHours(3)])->saveQuietly();

        $post->update(['image_caption' => 'New caption']);
        $post->increment('views');

        $this->assertFalse($post->fresh()->wasUpdated());
    }

    /* ---------- Ads ---------- */

    public function test_admin_ads_show_in_their_place_on_the_article(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AdSlotsManager::class)
            ->set('codes.article_top', '<div class="zz-top-ad">TOP</div>')
            ->set('codes.article_middle', '<div class="zz-mid-ad">MID</div>')
            ->call('toggle', 'article_top')
            ->call('toggle', 'article_middle')
            ->set('codes.sidebar', '<div class="zz-side-ad">SIDE</div>')
            ->call('save', 'sidebar')
            ->assertDispatched('admin-toast');

        $html = $this->get(route('posts.show', $this->makePost()))->assertOk()->getContent();

        $this->assertStringContainsString('zz-top-ad', $html);
        $this->assertStringContainsString('<p>Three</p><aside class="bm-ad bm-ad--article-middle"', preg_replace('/\s+(?=<)/', '', $html));
        $this->assertStringNotContainsString('zz-side-ad', $html, 'saved but switched off');

        $this->actingAs($this->admin)->get(route('admin.ads'))->assertOk()->assertSee('Article: in the middle');
    }

    public function test_ad_switch_needs_code_and_readers_cannot_manage_ads(): void
    {
        // HTTP check first: after a Livewire test call, redirect() returns Livewire's redirector
        $this->actingAs($this->reader)->get(route('admin.ads'))->assertRedirect(url('/'));

        Livewire::actingAs($this->admin)
            ->test(AdSlotsManager::class)
            ->call('toggle', 'sidebar')
            ->assertSet('active.sidebar', false);

        Livewire::actingAs($this->reader)->test(AdSlotsManager::class)->assertForbidden();
    }

    public function test_middle_ad_is_skipped_on_short_stories(): void
    {
        $this->assertSame('<p>One</p><p>Two</p>', AdSlot::insertAfterParagraph('<p>One</p><p>Two</p>', '[ad]'));
        $this->assertSame('<p>1</p><p>2</p><p>3</p>', AdSlot::insertAfterParagraph('<p>1</p><p>2</p><p>3</p>', '[ad]'));
        $this->assertSame('<p>1</p><p>2</p><p>3</p>[ad]<p>4</p>', AdSlot::insertAfterParagraph('<p>1</p><p>2</p><p>3</p><p>4</p>', '[ad]'));
    }

    /* ---------- Google News structured data ---------- */

    public function test_article_has_news_article_structured_data(): void
    {
        $post = $this->makePost(['title' => 'Zz Schema Story', 'author_name' => 'Chioma Agbomma']);
        $post->syncTags('Anambra');

        $html = $this->get(route('posts.show', $post))->assertOk()->getContent();

        preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $matches);
        $schema = collect($matches[1])->map(fn ($json) => json_decode($json, true))->firstWhere('@type', 'NewsArticle');

        $this->assertNotNull($schema, 'NewsArticle JSON-LD is on the page');
        $this->assertSame('Zz Schema Story', $schema['headline']);
        $this->assertSame('Chioma Agbomma', $schema['author'][0]['name']);
        $this->assertSame(url('/writer/chioma-agbomma'), $schema['author'][0]['url']);
        $this->assertSame(['Anambra'], $schema['keywords']);
        $this->assertArrayHasKey('dateModified', $schema);
        $this->assertStringStartsWith('http', $schema['image'][0], 'image URLs are absolute');
    }

    public function test_old_auto_saved_blog_posting_schema_is_replaced_by_news_article(): void
    {
        $post = $this->makePost();
        $post->forceFill(['schema_markup' => json_encode(['@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => 'old'])])->saveQuietly();

        $this->get(route('posts.show', $post))->assertOk()
            ->assertSee('"@type":"NewsArticle"', false)
            ->assertDontSee('"@type":"BlogPosting"', false);

        $custom = json_encode(['@context' => 'https://schema.org', '@type' => 'Event', 'name' => 'Zz Custom Event']);
        $post->forceFill(['schema_markup' => $custom])->saveQuietly();
        // saveQuietly skips the "saved" event that clears pages kept for guests (a normal save does it)
        Post::forgetHomeCache();

        $this->get(route('posts.show', $post))->assertOk()->assertSee('Zz Custom Event');
    }

    public function test_share_image_is_a_full_web_address(): void
    {
        $this->get(route('posts.show', $this->makePost()))->assertOk()
            ->assertSee('<meta property="og:image" content="http', false);
    }

    /* ---------- Image compression ---------- */

    public function test_uploaded_cover_photos_are_shrunk_to_webp_with_a_jpeg_copy(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $this->postForm([
                'title' => 'Zz Big Photo',
                'featured_image_1' => UploadedFile::fake()->image('huge.jpg', 3000, 2000),
                'featured_image_2' => UploadedFile::fake()->image('inline.png', 900, 600),
            ]))
            ->assertSessionHasNoErrors();

        $post = Post::where('title', 'Zz Big Photo')->firstOrFail();

        $this->assertStringEndsWith('.webp', $post->featured_image_1);
        Storage::disk('public')->assertExists($post->featured_image_1);
        [$width] = getimagesizefromstring(Storage::disk('public')->get($post->featured_image_1));
        $this->assertSame(1600, $width);

        $jpegCopy = preg_replace('/\.webp$/', '.jpg', $post->featured_image_1);
        Storage::disk('public')->assertExists($jpegCopy);
        $this->assertSame(url('storage/'.$jpegCopy), $post->shareImageUrl());

        $this->assertStringEndsWith('.webp', $post->featured_image_2);
        Storage::disk('public')->assertMissing(preg_replace('/\.webp$/', '.jpg', $post->featured_image_2));
    }

    /* ---------- Facebook / Telegram / push alerts ---------- */

    private function enableSharing(): void
    {
        config([
            'facebook.page_id' => 'zz-page',
            'facebook.access_token' => 'zz-token',
            'services.telegram.bot_token' => 'zz-bot',
            'services.telegram.channel_id' => '@zzchannel',
            'services.onesignal.app_id' => 'zz-app',
            'services.onesignal.rest_api_key' => 'zz-key',
        ]);
    }

    public function test_publishing_shares_once_and_drafts_are_never_shared(): void
    {
        $this->enableSharing();

        $draft = $this->makePost(['title' => 'Zz Draft Not Shared', 'status' => 'draft']);
        Http::assertNothingSent();

        $draft->update(['status' => 'published']);
        Http::assertSentCount(2);
        Http::assertSent(fn (HttpRequest $request) => str_contains($request->url(), 'graph.facebook.com/') && $request['message'] === 'Zz Draft Not Shared');
        Http::assertSent(fn (HttpRequest $request) => str_contains($request->url(), 'api.telegram.org/botzz-bot/sendPhoto') && $request['chat_id'] === '@zzchannel');

        $draft->update(['title' => 'Zz Draft Not Shared (edited)']);
        Http::assertSentCount(2);
    }

    public function test_breaking_news_sends_one_push_alert(): void
    {
        $this->enableSharing();

        $post = $this->makePost(['title' => 'Zz Breaking Alert', 'breaking_until' => now()->addDay()]);

        Http::assertSent(fn (HttpRequest $request) => $request->url() === 'https://api.onesignal.com/notifications'
            && $request['contents']['en'] === 'Zz Breaking Alert'
            && $request->hasHeader('Authorization', 'Key zz-key'));

        $post->update(['excerpt' => 'More detail']);
        Http::assertSentCount(3);
    }

    public function test_scheduled_posts_are_shared_when_they_go_live_and_old_posts_are_not_reshared(): void
    {
        $this->enableSharing();

        $old = $this->makePost(['title' => 'Zz Old Story']);
        $old->forceFill(['announced_at' => now()->subYear(), 'push_sent_at' => now()->subYear()])->saveQuietly();
        Http::fake();
        $old->update(['title' => 'Zz Old Story edited']);
        Http::assertNothingSent();

        $scheduled = $this->makePost(['title' => 'Zz Scheduled Share', 'status' => 'draft', 'publish_at' => now()->subMinute()]);
        Http::assertNothingSent();

        Post::publishDueScheduled();
        Http::assertSent(fn (HttpRequest $request) => str_contains($request->url(), 'graph.facebook.com/') && $request['message'] === 'Zz Scheduled Share');
        $this->assertNotNull($scheduled->fresh()->announced_at);
    }

    public function test_push_service_worker_is_served_from_the_site_root(): void
    {
        $this->get('/OneSignalSDKWorker.js')->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8')
            ->assertSee('OneSignalSDK.sw.js');
    }
    /* ---------- Images in emails and link previews ---------- */

    public function test_newsletter_images_use_full_web_addresses(): void
    {
        Storage::disk('public')->put('posts/featured/zz-cover.webp', 'x');
        Storage::disk('public')->put('posts/featured/zz-cover.jpg', 'x');
        $post = $this->makePost(['title' => 'Zz Email Story', 'featured_image_1' => 'posts/featured/zz-cover.webp']);
        $subscriber = Subscriber::create(['email' => 'zz-img@example.com', 'is_active' => true, 'unsubscribe_token' => 'zz-img-token']);

        $html = (new NewsletterMail(collect([$post]), collect(), $subscriber))->render();

        $this->assertStringContainsString('src="'.url('storage/posts/featured/zz-cover.jpg').'"', $html, 'JPEG copy, full address');
        $this->assertStringNotContainsString('src="/storage/', $html);
    }

    public function test_story_page_tells_facebook_the_image_size(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $this->postForm(['title' => 'Zz Sized Photo', 'featured_image_1' => UploadedFile::fake()->image('wide.jpg', 1800, 945)]))
            ->assertSessionHasNoErrors();

        $post = Post::where('title', 'Zz Sized Photo')->firstOrFail();

        auth()->logout();
        $this->get(route('posts.show', $post))->assertOk()
            ->assertSee('<meta property="og:image" content="'.url('storage/'.preg_replace('/\.webp$/', '.jpg', $post->featured_image_1)).'">', false)
            ->assertSee('<meta property="og:image:width" content="1200">', false)
            ->assertSee('<meta property="og:image:height" content="630">', false);
    }
}
