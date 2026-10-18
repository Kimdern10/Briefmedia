<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostImagesTest extends TestCase
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

        $this->admin = User::factory()->create(['password' => 'password']);
        $this->admin->forceFill(['role_as' => 1])->save();
        $this->category = Category::firstOrCreate(['name' => 'Zz Image Category']);
    }

    /**
     * A post whose cover is an old, full-size upload (as before compression existed).
     */
    private function postWithOldPhoto(string $title, int $width = 3000, int $height = 2000): Post
    {
        $path = UploadedFile::fake()->image('old.jpg', $width, $height)->store('posts/featured', 'public');

        return Post::create([
            'admin_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => $title,
            'content' => '<p>Body</p>',
            'featured_image_1' => $path,
            'status' => 'published',
        ]);
    }

    public function test_new_cover_uploads_get_a_small_card_version(): void
    {
        $this->actingAs($this->admin)->post(route('admin.posts.store'), [
            'category_id' => $this->category->id,
            'title' => 'Zz Fresh Upload',
            'content' => '<p>Body</p>',
            'status' => 'published',
            'featured_image_1' => UploadedFile::fake()->image('cover.jpg', 2400, 1600),
        ])->assertSessionHasNoErrors();

        $post = Post::where('title', 'Zz Fresh Upload')->firstOrFail();
        $card = preg_replace('/\.webp$/', '-640.webp', $post->featured_image_1);

        Storage::disk('public')->assertExists($card);
        [$width] = getimagesizefromstring(Storage::disk('public')->get($card));
        $this->assertSame(640, $width);
        $this->assertSame(asset('storage/'.$card), $post->coverUrl('card'));
        $this->assertSame(asset('storage/'.$post->featured_image_1), $post->coverUrl());
    }

    public function test_cards_use_the_small_version_and_fall_back_to_the_full_photo(): void
    {
        $old = $this->postWithOldPhoto('Zz Old Photo Story');

        // Not optimised yet: cards still show the original
        $this->assertSame(asset('storage/'.$old->featured_image_1), $old->coverUrl('card'));

        $this->artisan('images:optimize')->assertSuccessful();
        $old->refresh();

        $this->get(url('/'))->assertOk()
            ->assertSee(asset('storage/'.preg_replace('/\.webp$/', '-640.webp', $old->featured_image_1)), false);
    }

    public function test_command_shrinks_old_photos_and_keeps_the_originals(): void
    {
        $post = $this->postWithOldPhoto('Zz Shrink Me');
        $original = $post->featured_image_1;
        $updatedAt = $post->updated_at;

        $this->travel(5)->minutes();
        $this->artisan('images:optimize')
            ->expectsOutputToContain('1 post(s) updated.')
            ->expectsOutputToContain('All post photos are optimised.')
            ->assertSuccessful();

        $post->refresh();
        $disk = Storage::disk('public');

        $this->assertStringEndsWith('.webp', $post->featured_image_1);
        $disk->assertExists($original);
        $disk->assertExists(preg_replace('/\.webp$/', '-640.webp', $post->featured_image_1));
        $disk->assertExists(preg_replace('/\.webp$/', '.jpg', $post->featured_image_1));

        [$width] = getimagesizefromstring($disk->get($post->featured_image_1));
        $this->assertSame(1600, $width);
        $this->assertLessThan($disk->size($original), $disk->size($post->featured_image_1));

        // Doesn't count as an edit of the story
        $this->assertTrue($post->updated_at->equalTo($updatedAt));
        $this->assertFalse($post->wasUpdated());

        // Running again has nothing left to do
        $this->artisan('images:optimize')->expectsOutputToContain('0 post(s) updated.')->assertSuccessful();
    }

    public function test_command_works_in_batches_and_can_preview(): void
    {
        $this->postWithOldPhoto('Zz Batch One', 800, 600);
        $this->postWithOldPhoto('Zz Batch Two', 800, 600);
        $this->postWithOldPhoto('Zz Batch Three', 800, 600);

        $this->artisan('images:optimize', ['--dry-run' => true])
            ->expectsOutputToContain('would be updated')
            ->assertSuccessful();
        $this->assertSame(3, Post::where('title', 'like', 'Zz Batch%')->where('featured_image_1', 'not like', '%.webp')->count());

        $this->artisan('images:optimize', ['--limit' => 2])
            ->expectsOutputToContain('2 post(s) updated.')
            ->assertSuccessful();

        $this->assertSame(1, Post::where('title', 'like', 'Zz Batch%')->where('featured_image_1', 'not like', '%.webp')->count());
    }

    private function putWebp(string $path, int $width, int $height): void
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        imagewebp($image);
        Storage::disk('public')->put($path, ob_get_clean());
    }

    public function test_command_redoes_webp_photos_that_were_left_full_size(): void
    {
        // What a server whose resizing failed produced: a full-size WebP and a full-size "card"
        $this->putWebp('posts/featured/zz-big.webp', 2560, 1920);
        $this->putWebp('posts/featured/zz-big-640.webp', 2560, 1920);
        $this->putWebp('posts/featured/zz-inline.webp', 2400, 1600);

        $post = Post::create([
            'admin_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'Zz Too Big Webp',
            'content' => '<p>Body</p>',
            'featured_image_1' => 'posts/featured/zz-big.webp',
            'featured_image_2' => 'posts/featured/zz-inline.webp',
            'status' => 'published',
        ]);

        $this->artisan('images:optimize')->expectsOutputToContain('All post photos are optimised.')->assertSuccessful();

        $post->refresh();
        $disk = Storage::disk('public');
        $this->assertNotSame('posts/featured/zz-big.webp', $post->featured_image_1);
        $this->assertSame(1600, getimagesizefromstring($disk->get($post->featured_image_1))[0]);
        $this->assertSame(640, getimagesizefromstring($disk->get(preg_replace('/\.webp$/', '-640.webp', $post->featured_image_1)))[0]);
        $this->assertSame(1600, getimagesizefromstring($disk->get($post->featured_image_2))[0]);
    }

    public function test_command_shrinks_a_card_version_that_is_too_big(): void
    {
        $this->putWebp('posts/featured/zz-ok.webp', 1200, 800);
        $this->putWebp('posts/featured/zz-ok-640.webp', 1200, 800);

        Post::create([
            'admin_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'Zz Card Too Big',
            'content' => '<p>Body</p>',
            'featured_image_1' => 'posts/featured/zz-ok.webp',
            'status' => 'published',
        ]);

        $this->artisan('images:optimize')->expectsOutputToContain('card version')->assertSuccessful();

        $this->assertSame(640, getimagesizefromstring(Storage::disk('public')->get('posts/featured/zz-ok-640.webp'))[0]);
    }

    public function test_already_compressed_covers_just_get_their_card_version(): void
    {
        $webp = 'posts/featured/zz-ready.webp';
        $image = imagecreatetruecolor(1200, 800);
        ob_start();
        imagewebp($image);
        Storage::disk('public')->put($webp, ob_get_clean());

        $post = Post::create([
            'admin_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'Zz Ready Webp',
            'content' => '<p>Body</p>',
            'featured_image_1' => $webp,
            'status' => 'published',
        ]);

        $this->artisan('images:optimize')->assertSuccessful();

        $this->assertSame($webp, $post->fresh()->featured_image_1);
        Storage::disk('public')->assertExists('posts/featured/zz-ready-640.webp');
    }
}
