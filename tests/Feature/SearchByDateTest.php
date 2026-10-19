<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SearchByDateTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->admin = User::factory()->create(['password' => 'password']);
        $this->category = Category::firstOrCreate(['name' => 'Zz Date Search Category']);

        $this->story('Zz Soludo March 2024', '2024-03-10 09:00:00');
        $this->story('Zz Budget March 2024', '2024-03-22 15:00:00');
        $this->story('Zz Soludo October 2024', '2024-10-17 08:00:00');
        $this->story('Zz Soludo March 2023', '2023-03-05 12:00:00');
        $this->story('Zz Draft March 2024', '2024-03-11 10:00:00', 'draft');
    }

    private function story(string $title, string $date, string $status = 'published'): Post
    {
        $post = Post::create([
            'admin_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => $title,
            'content' => '<p>'.$title.'</p>',
            'featured_image_1' => 'posts/featured/x.jpg',
            'status' => $status,
        ]);
        $post->forceFill(['created_at' => Carbon::parse($date)])->saveQuietly();

        return $post;
    }

    /**
     * @return array<int, string>
     */
    private function titles(array $filters): array
    {
        $response = $this->get(route('user.posts.search', $filters))->assertOk();

        return collect($response->viewData('posts')->items())
            ->pluck('title')
            ->filter(fn ($title) => str_starts_with($title, 'Zz '))
            ->sort()->values()->all();
    }

    public function test_search_by_month_and_year_without_a_keyword(): void
    {
        $this->assertSame(['Zz Budget March 2024', 'Zz Soludo March 2024'], $this->titles(['month' => 3, 'year' => 2024]));

        $this->get(route('user.posts.search', ['month' => 3, 'year' => 2024]))
            ->assertSee('Stories from <em>March 2024</em>', false)
            ->assertDontSee('Zz Draft March 2024');
    }

    public function test_search_by_year_or_month_alone(): void
    {
        $this->assertSame(['Zz Budget March 2024', 'Zz Soludo March 2024', 'Zz Soludo October 2024'], $this->titles(['year' => 2024]));
        $this->assertSame(['Zz Budget March 2024', 'Zz Soludo March 2023', 'Zz Soludo March 2024'], $this->titles(['month' => 3]));
    }

    public function test_search_by_exact_day(): void
    {
        $this->assertSame(['Zz Soludo October 2024'], $this->titles(['date' => '2024-10-17']));

        $this->get(route('user.posts.search', ['date' => '2024-10-17']))->assertSee('17 October 2024');
    }

    public function test_keyword_and_date_together(): void
    {
        $this->assertSame(['Zz Soludo March 2024'], $this->titles(['query' => 'Zz Soludo', 'month' => 3, 'year' => 2024]));

        $this->get(route('user.posts.search', ['query' => 'Zz Soludo', 'month' => 3, 'year' => 2024]))
            ->assertSee('in March 2024');
    }

    public function test_empty_search_shows_the_form_and_bad_values_are_ignored(): void
    {
        $this->get(route('user.posts.search'))->assertOk()
            ->assertSee('Find a story')
            ->assertSee('name="month"', false)
            ->assertSee('<option value="2024"', false);

        $this->get(route('user.posts.search', ['month' => 13, 'year' => 1800, 'date' => 'not-a-date']))
            ->assertOk()
            ->assertSee('Find a story');
    }
}
