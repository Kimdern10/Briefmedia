<?php

namespace Tests\Feature;

use App\Livewire\LiveSearch;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class LiveSearchTest extends TestCase
{
    use DatabaseTransactions;

    private Post $published;

    private Post $draft;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create(['password' => 'password']);
        $category = Category::create(['name' => 'Zz Live Search Category']);

        $make = fn (string $title, string $status) => Post::create([
            'admin_id' => $admin->id,
            'category_id' => $category->id,
            'title' => $title,
            'excerpt' => 'Excerpt for '.$title,
            'content' => '<p>'.$title.'</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => $status,
        ]);

        $this->published = $make('Narwhal Festival Opens In Awka', 'published');
        $this->draft = $make('Narwhal Draft Not Ready Yet', 'draft');
    }

    public function test_typing_shows_matching_published_stories_only(): void
    {
        Livewire::test(LiveSearch::class)
            ->set('query', 'Narwhal')
            ->assertSee($this->published->title)
            ->assertSee('Zz Live Search Category')
            ->assertSee(route('posts.show', $this->published), false)
            ->assertDontSee($this->draft->title)
            ->assertSee('See all results');
    }

    public function test_short_queries_show_the_hint_instead_of_results(): void
    {
        Livewire::test(LiveSearch::class)
            ->set('query', 'N')
            ->assertSee('Type at least 2 letters')
            ->assertDontSee($this->published->title);
    }

    public function test_no_match_shows_an_empty_message(): void
    {
        Livewire::test(LiveSearch::class)
            ->set('query', 'Xylophonezzz')
            ->assertSee('No stories match');
    }

    public function test_pages_load_livewire_once_and_no_second_copy_of_alpine(): void
    {
        $this->get(url('/'))
            ->assertOk()
            ->assertSeeLivewire(LiveSearch::class)
            ->assertSee('livewire.js', false)
            ->assertDontSee('cdn.jsdelivr.net/npm/alpinejs', false);

        // Like buttons (Alpine x-data) still render for logged-in readers
        $this->actingAs(User::factory()->create(['password' => 'password']))
            ->get(route('posts.show', $this->published))
            ->assertOk()
            ->assertSee('x-data', false)
            ->assertSee('livewire.js', false);
    }
}
