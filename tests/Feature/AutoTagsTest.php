<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\AutoTagger;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AutoTagsTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Category $category;

    private const STORY = '<p>Governor Charles Soludo on Monday met INEC officials in Awka South. Soludo said the '
        .'Anambra election will hold as planned.</p><p>According to INEC, voting in Awka South and Onitsha North '
        .'went smoothly. The Certificate of Return will be issued on Friday, INEC added.</p>'
        .'<p>Charles Soludo thanked voters across Anambra.</p>';

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Storage::fake('public');
        DB::table('post_tag')->delete();

        $this->admin = User::factory()->create(['password' => 'password']);
        $this->admin->forceFill(['role_as' => 1])->save();
        $this->category = Category::firstOrCreate(['name' => 'Zz Auto Tag Category']);
    }

    private function draftPost(array $attributes = []): Post
    {
        return new Post(array_merge([
            'title' => 'Soludo Meets INEC Ahead Of Anambra Election, Says Polls Will Hold',
            'excerpt' => '',
            'content' => self::STORY,
        ], $attributes));
    }

    public function test_it_picks_names_places_and_organisations_from_the_story(): void
    {
        $tags = AutoTagger::suggest($this->draftPost());

        $this->assertContains('Charles Soludo', $tags);
        $this->assertContains('INEC', $tags);
        $this->assertContains('Anambra', $tags);
        $this->assertContains('Awka South', $tags);
        $this->assertLessThanOrEqual(AutoTagger::MAX_TAGS, count($tags));

        // Not: titles, weekdays, headline verbs, or "Soludo" twice
        foreach (['Governor', 'Monday', 'Friday', 'Says', 'Meets', 'Soludo', 'According'] as $noise) {
            $this->assertNotContains($noise, $tags);
        }
    }

    public function test_title_case_headlines_do_not_become_tags(): void
    {
        $tags = AutoTagger::suggest($this->draftPost(['title' => 'Breaking: Officials Say Everything Went Smoothly In The Big Vote']));

        $this->assertNotContains('Officials Say Everything', $tags);
        $this->assertNotContains('Big Vote', $tags);
    }

    public function test_existing_topics_mentioned_in_the_story_are_reused(): void
    {
        Tag::firstOrCreate(['slug' => 'onitsha-north'], ['name' => 'Onitsha North']);
        Tag::firstOrCreate(['slug' => 'may'], ['name' => 'May']);

        $tags = AutoTagger::suggest($this->draftPost(['content' => self::STORY.'<p>Results may come soon.</p>']));

        $this->assertContains('Onitsha North', $tags);
        $this->assertNotContains('May', $tags, 'one-word tags match as written, not "may"');
    }

    public function test_an_empty_tags_box_gets_automatic_tags_and_typed_tags_are_kept(): void
    {
        $form = fn (array $extra) => array_merge([
            'category_id' => $this->category->id,
            'title' => 'Soludo Meets INEC Ahead Of Anambra Election',
            'content' => self::STORY,
            'status' => 'published',
            'featured_image_1' => UploadedFile::fake()->image('cover.jpg'),
        ], $extra);

        $this->actingAs($this->admin)->post(route('admin.posts.store'), $form(['tags' => '']))->assertSessionHasNoErrors();
        $auto = Post::where('title', 'Soludo Meets INEC Ahead Of Anambra Election')->firstOrFail();
        $this->assertContains('INEC', $auto->tags()->pluck('name')->all());

        $this->actingAs($this->admin)->post(route('admin.posts.store'), $form(['title' => 'Zz Typed Tags', 'tags' => 'My Own Tag']))->assertSessionHasNoErrors();
        $typed = Post::where('title', 'Zz Typed Tags')->firstOrFail();
        $this->assertSame(['My Own Tag'], $typed->tags()->pluck('name')->all());
    }

    public function test_command_tags_old_stories_without_tags_only(): void
    {
        $untagged = Post::create([
            'admin_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'Zz Old Untagged Story',
            'content' => self::STORY,
            'featured_image_1' => 'posts/featured/x.jpg',
            'status' => 'published',
        ]);
        $alreadyTagged = Post::create([
            'admin_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'Zz Already Tagged',
            'content' => self::STORY,
            'featured_image_1' => 'posts/featured/x.jpg',
            'status' => 'published',
        ]);
        $alreadyTagged->syncTags('Hand Picked');

        $this->artisan('tags:auto', ['--dry-run' => true])->expectsOutputToContain('would get tags')->assertSuccessful();
        $this->assertSame(0, $untagged->tags()->count());

        $this->artisan('tags:auto')->expectsOutputToContain('tagged.')->assertSuccessful();

        $this->assertContains('INEC', $untagged->tags()->pluck('name')->all());
        $this->assertSame(['Hand Picked'], $alreadyTagged->tags()->pluck('name')->all());
    }
}
