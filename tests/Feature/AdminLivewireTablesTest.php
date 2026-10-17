<?php

namespace Tests\Feature;

use App\Livewire\Admin\CategoriesTable;
use App\Livewire\Admin\CommentsTable;
use App\Livewire\Admin\PostsTable;
use App\Livewire\Admin\SubscribersTable;
use App\Livewire\Admin\UsersTable;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLivewireTablesTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['password' => 'password']);
        $this->admin->forceFill(['role_as' => 1])->save();
        $this->category = Category::firstOrCreate(['name' => 'Zz Admin Tables Category']);
    }

    private function makePost(string $title, string $status = 'draft'): Post
    {
        return Post::create([
            'admin_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => $title,
            'excerpt' => 'Excerpt',
            'content' => '<p>Body</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => $status,
        ]);
    }

    public function test_non_admins_cannot_use_admin_components_even_through_livewire(): void
    {
        $reader = User::factory()->create(['password' => 'password']);
        $post = $this->makePost('Zz Protected Post', 'published');

        foreach ([PostsTable::class, CategoriesTable::class, CommentsTable::class, SubscribersTable::class, UsersTable::class] as $component) {
            Livewire::actingAs($reader)->test($component)->assertForbidden();
        }

        $this->assertNull($post->fresh()->deleted_at);
    }

    public function test_posts_publish_trash_restore_and_delete_forever(): void
    {
        $post = $this->makePost('Zz Lifecycle Post');

        Livewire::actingAs($this->admin)
            ->test(PostsTable::class, ['mode' => 'active'])
            ->set('search', 'Zz Lifecycle')
            ->assertSee('Zz Lifecycle Post')
            ->call('toggleStatus', $post->id)
            ->assertDispatched('admin-toast');
        $this->assertSame('published', $post->fresh()->status);

        Livewire::actingAs($this->admin)
            ->test(PostsTable::class, ['mode' => 'active'])
            ->call('trash', $post->id)
            ->assertNoRedirect();
        $this->assertSoftDeleted($post);

        Livewire::actingAs($this->admin)
            ->test(PostsTable::class, ['mode' => 'trash'])
            ->assertSee('Zz Lifecycle Post')
            ->call('restore', $post->id);
        $this->assertNotSoftDeleted($post);

        $post->delete();
        Livewire::actingAs($this->admin)
            ->test(PostsTable::class, ['mode' => 'trash'])
            ->call('forceDelete', $post->id);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_status_tabs_and_search_filter_posts(): void
    {
        $this->makePost('Zz Tab Draft', 'draft');
        $this->makePost('Zz Tab Published', 'published');

        Livewire::actingAs($this->admin)
            ->test(PostsTable::class, ['mode' => 'active'])
            ->set('search', 'Zz Tab')
            ->call('setStatus', 'draft')
            ->assertSee('Zz Tab Draft')
            ->assertDontSee('Zz Tab Published')
            ->call('setStatus', 'published')
            ->assertSee('Zz Tab Published')
            ->assertDontSee('Zz Tab Draft');
    }

    public function test_categories_trash_restore_and_delete_forever(): void
    {
        $category = Category::create(['name' => 'Zz Disposable Category']);

        Livewire::actingAs($this->admin)->test(CategoriesTable::class, ['mode' => 'active'])
            ->set('search', 'Disposable')
            ->assertSee('Zz Disposable Category')
            ->call('trash', $category->id);
        $this->assertSoftDeleted($category);

        Livewire::actingAs($this->admin)->test(CategoriesTable::class, ['mode' => 'trash'])
            ->call('restore', $category->id);
        $this->assertNotSoftDeleted($category);

        $category->delete();
        Livewire::actingAs($this->admin)->test(CategoriesTable::class, ['mode' => 'trash'])
            ->call('forceDelete', $category->id);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_comments_can_be_searched_and_deleted(): void
    {
        $post = $this->makePost('Zz Commented Post', 'published');
        $comment = Comment::create(['post_id' => $post->id, 'user_id' => $this->admin->id, 'content' => 'Zz spammy comment']);

        Livewire::actingAs($this->admin)->test(CommentsTable::class)
            ->set('search', 'Zz spammy')
            ->assertSee('Zz spammy comment')
            ->call('delete', $comment->id)
            ->assertDontSee('Zz spammy comment');

        $this->assertDatabaseMissing('post_comments', ['id' => $comment->id]);
    }

    public function test_subscribers_can_be_searched_and_removed(): void
    {
        $subscriber = Subscriber::create(['email' => 'zz-remove-me@example.com', 'is_active' => true, 'unsubscribe_token' => 'zz']);

        Livewire::actingAs($this->admin)->test(SubscribersTable::class)
            ->set('search', 'zz-remove-me')
            ->assertSee('zz-remove-me@example.com')
            ->call('delete', $subscriber->id)
            ->assertDontSee('zz-remove-me@example.com');

        $this->assertDatabaseMissing('subscribers', ['id' => $subscriber->id]);
    }

    public function test_users_ban_unban_delete_restore_and_delete_forever(): void
    {
        $reader = User::factory()->create(['password' => 'password', 'name' => 'Zz Ban Target']);

        $table = Livewire::actingAs($this->admin)->test(UsersTable::class, ['mode' => 'active'])
            ->set('search', 'Zz Ban Target')
            ->call('ban', $reader->id);
        $this->assertSame(0, (int) $reader->fresh()->active);

        $table->call('unban', $reader->id);
        $this->assertSame(1, (int) $reader->fresh()->active);

        $table->call('delete', $reader->id);
        $this->assertSoftDeleted($reader);

        Livewire::actingAs($this->admin)->test(UsersTable::class, ['mode' => 'trash'])
            ->call('restore', $reader->id);
        $this->assertNotSoftDeleted($reader);

        $reader->delete();
        Livewire::actingAs($this->admin)->test(UsersTable::class, ['mode' => 'trash'])
            ->call('forceDelete', $reader->id);
        $this->assertDatabaseMissing('users', ['id' => $reader->id]);
    }

    public function test_admin_accounts_cannot_be_banned_or_deleted_from_the_users_table(): void
    {
        $otherAdmin = User::factory()->create(['password' => 'password']);
        $otherAdmin->forceFill(['role_as' => 1])->save();

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($this->admin)->test(UsersTable::class, ['mode' => 'active'])
            ->call('ban', $otherAdmin->id);
    }

    public function test_mode_cannot_be_changed_from_the_browser(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::actingAs($this->admin)->test(PostsTable::class, ['mode' => 'active'])
            ->set('mode', 'trash');
    }
}
