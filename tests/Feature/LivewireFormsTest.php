<?php

namespace Tests\Feature;

use App\Livewire\NewsletterSignup;
use App\Livewire\PostComments;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireFormsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('newsletter:127.0.0.1');
    }

    private function makePost(): Post
    {
        $admin = User::factory()->create(['password' => 'password']);
        $category = Category::firstOrCreate(['name' => 'Zz Livewire Forms Category']);

        return Post::create([
            'admin_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Zz Livewire Forms Story',
            'excerpt' => 'Excerpt',
            'content' => '<p>Body</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => 'published',
        ]);
    }

    public function test_subscribing_shows_a_confirmation_without_leaving_the_page(): void
    {
        Livewire::test(NewsletterSignup::class)
            ->set('email', 'New-Reader@Example.com')
            ->call('subscribe')
            ->assertHasNoErrors()
            ->assertSee("You're subscribed!")
            ->assertNoRedirect();

        $this->assertTrue(Subscriber::where('email', 'new-reader@example.com')->where('is_active', true)->exists());
    }

    public function test_subscribing_twice_and_reactivating_are_handled(): void
    {
        Subscriber::create(['email' => 'active@example.com', 'is_active' => true, 'unsubscribe_token' => 'a']);
        Subscriber::create(['email' => 'gone@example.com', 'is_active' => false, 'unsubscribe_token' => 'b']);

        Livewire::test(NewsletterSignup::class)->set('email', 'active@example.com')->call('subscribe')
            ->assertSee('already subscribed');

        Livewire::test(NewsletterSignup::class)->set('email', 'gone@example.com')->call('subscribe')
            ->assertSee('Welcome back');

        $this->assertTrue((bool) Subscriber::where('email', 'gone@example.com')->value('is_active'));
        $this->assertSame(1, Subscriber::where('email', 'active@example.com')->count());
    }

    public function test_invalid_email_shows_an_error(): void
    {
        Livewire::test(NewsletterSignup::class)
            ->set('email', 'not-an-email')
            ->call('subscribe')
            ->assertHasErrors(['email' => 'email']);
    }

    public function test_logged_in_reader_can_comment_and_reply_without_reload(): void
    {
        $post = $this->makePost();
        $reader = User::factory()->create(['password' => 'password']);

        $component = Livewire::actingAs($reader)
            ->test(PostComments::class, ['post' => $post])
            ->set('content', 'Great story, thanks!')
            ->call('postComment')
            ->assertHasNoErrors()
            ->assertSet('content', '')
            ->assertSee('Great story, thanks!')
            ->assertNoRedirect();

        $comment = Comment::where('post_id', $post->id)->firstOrFail();

        $component->call('startReply', $comment->id)
            ->set('replyContent', 'Agreed!')
            ->call('postReply')
            ->assertHasNoErrors()
            ->assertSee('Agreed!');

        $this->assertDatabaseHas('post_comments', ['parent_id' => $comment->id, 'content' => 'Agreed!', 'user_id' => $reader->id]);
    }

    public function test_empty_comment_is_rejected(): void
    {
        $post = $this->makePost();

        Livewire::actingAs(User::factory()->create(['password' => 'password']))
            ->test(PostComments::class, ['post' => $post])
            ->set('content', '')
            ->call('postComment')
            ->assertHasErrors(['content' => 'required']);
    }

    public function test_guests_are_sent_to_login_when_trying_to_comment(): void
    {
        $post = $this->makePost();

        Livewire::test(PostComments::class, ['post' => $post])
            ->assertSee('Log in to comment')
            ->set('content', 'Hello')
            ->call('postComment')
            ->assertRedirect(route('login'));

        $this->assertSame(0, Comment::where('post_id', $post->id)->count());
    }

    public function test_reply_cannot_target_a_comment_on_another_post(): void
    {
        $post = $this->makePost();
        $otherPost = $this->makePost();
        $reader = User::factory()->create(['password' => 'password']);
        $foreignComment = Comment::create(['post_id' => $otherPost->id, 'user_id' => $reader->id, 'content' => 'Elsewhere']);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($reader)
            ->test(PostComments::class, ['post' => $post])
            ->set('replyingTo', $foreignComment->id)
            ->set('replyContent', 'Sneaky reply')
            ->call('postReply');
    }
}
