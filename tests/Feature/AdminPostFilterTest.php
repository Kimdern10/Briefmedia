<?php

namespace Tests\Feature;

use App\Livewire\Admin\PostsTable;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPostFilterTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Post $oldDraft;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['password' => 'password']);
        $this->admin->forceFill(['role_as' => 1])->save();

        $category = Category::create(['name' => 'Zz Filter Test Category']);

        // An old draft followed by a full page of newer published posts,
        // so the draft is not on page 1 of the unfiltered list.
        $this->oldDraft = $this->makePost($category, 'Quokka Draft Hidden On Page Two', 'draft', now()->subYears(5));

        foreach (range(1, 12) as $number) {
            $this->makePost($category, "Wombat Published Story {$number}", 'published', now()->addMinutes($number));
        }
    }

    private function makePost(Category $category, string $title, string $status, $createdAt): Post
    {
        $post = Post::create([
            'admin_id' => $this->admin->id,
            'category_id' => $category->id,
            'title' => $title,
            'excerpt' => 'Excerpt for '.$title,
            'content' => '<p>'.$title.'</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => $status,
        ]);
        $post->forceFill(['created_at' => $createdAt])->save();

        return $post;
    }

    public function test_drafts_filter_finds_drafts_beyond_the_first_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.posts.index'))
            ->assertOk()
            ->assertDontSee($this->oldDraft->title);

        $this->actingAs($this->admin)
            ->get(route('admin.posts.index', ['status' => 'draft']))
            ->assertOk()
            ->assertSee($this->oldDraft->title)
            ->assertDontSee('Wombat Published Story');
    }

    public function test_published_filter_hides_drafts(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.posts.index', ['status' => 'published', 'q' => 'Quokka']))
            ->assertOk()
            ->assertDontSee($this->oldDraft->title)
            ->assertSee('No published posts');
    }

    public function test_search_covers_every_post_not_just_the_current_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.posts.index', ['q' => 'Quokka']))
            ->assertOk()
            ->assertSee($this->oldDraft->title)
            ->assertDontSee('Wombat Published Story');
    }

    public function test_search_and_status_combine_and_keep_their_values_in_pagination(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.posts.index', ['q' => 'Wombat', 'status' => 'published']))
            ->assertOk()
            ->assertSee('Wombat Published Story 12')
            ->assertDontSee($this->oldDraft->title);

        // 12 matches at 10 per page: going to page 2 keeps the search and status filter
        Livewire::actingAs($this->admin)
            ->withQueryParams(['q' => 'Wombat', 'status' => 'published'])
            ->test(PostsTable::class, ['mode' => 'active'])
            ->assertSet('search', 'Wombat')
            ->assertSet('status', 'published')
            ->call('nextPage')
            ->assertSee('Wombat Published Story 1')
            ->assertSee('Wombat Published Story 2')
            ->assertDontSee('Wombat Published Story 12')
            ->assertDontSee($this->oldDraft->title);
    }
}
