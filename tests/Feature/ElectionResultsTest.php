<?php

namespace Tests\Feature;

use App\Livewire\Admin\ElectionResultsManager;
use App\Livewire\ElectionResults;
use App\Models\Category;
use App\Models\Election;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class ElectionResultsTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['password' => 'password']);
        $this->admin->forceFill(['role_as' => 1])->save();

        $this->post = Post::create([
            'admin_id' => $this->admin->id,
            'category_id' => Category::firstOrCreate(['name' => 'Zz Election Category'])->id,
            'title' => 'Zz Anambra Decides',
            'content' => '<p>Body</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => 'published',
        ]);
    }

    private function setUpBoard(): Election
    {
        return Election::setUp($this->post, 'Anambra Governorship Election', 'LGA',
            ['Charles Soludo | APGA | #1f8a4c', 'Nicholas Ukachukwu | APC', 'George Moghalu | LP'],
            ['Awka South', 'Onitsha North', 'Nnewi North']);
    }

    public function test_admin_sets_up_a_board_and_enters_results_by_lga(): void
    {
        $component = Livewire::actingAs($this->admin)
            ->test(ElectionResultsManager::class, ['post' => $this->post])
            ->assertSee('Add results board')
            ->call('startSetup')
            ->assertSet('title', 'Zz Anambra Decides')
            ->set('title', 'Anambra Governorship Election')
            ->set('candidatesText', "Charles Soludo | APGA | #1f8a4c\nNicholas Ukachukwu | APC\nGeorge Moghalu | LP\n")
            ->set('areasText', "Awka South\nOnitsha North\n\nNnewi North")
            ->call('saveSetup')
            ->assertHasNoErrors()
            ->assertSet('editingSetup', false);

        $election = $this->post->election()->with(['candidates', 'areas'])->first();
        $this->assertSame(['Charles Soludo', 'Nicholas Ukachukwu', 'George Moghalu'], $election->candidates->pluck('name')->all());
        $this->assertSame(['APGA', 'APC', 'LP'], $election->candidates->pluck('party')->all());
        $this->assertSame('#1f8a4c', $election->candidates->first()->color);
        $this->assertCount(3, $election->areas);

        [$apga, $apc, $lp] = $election->candidates->all();
        $awka = $election->areas->first();

        $component
            ->set("votes.{$awka->id}.{$apga->id}", 12400)
            ->set("votes.{$awka->id}.{$apc->id}", 8100)
            ->set("votes.{$awka->id}.{$lp->id}", 3000)
            ->set("reported.{$awka->id}", true)
            ->call('saveArea', $awka->id)
            ->assertHasNoErrors()
            ->assertDispatched('admin-toast')
            ->assertSee('1 of 3 LGAs reported');

        $standings = $election->fresh(['candidates', 'areas'])->standings($election->votesByArea());
        $this->assertSame('Charles Soludo', $standings->first()->name);
        $this->assertSame(12400, $standings->first()->total);
        $this->assertEquals(52.8, $standings->first()->percent);
    }

    public function test_readers_see_totals_lga_table_and_leader_on_the_story(): void
    {
        $election = $this->setUpBoard();
        [$apga, $apc] = $election->candidates->all();
        [$awka, $onitsha] = $election->areas->all();

        $election->recordArea($awka, [$apga->id => 12400, $apc->id => 8100], true);
        $election->recordArea($onitsha, [$apga->id => 5000, $apc->id => 9000], false);

        $this->get(route('posts.show', $this->post))->assertOk()
            ->assertSee('Anambra Governorship Election')
            ->assertSee('Results by LGA')
            ->assertSee('17,400')
            ->assertSee('Leading')
            ->assertSee('Partial')
            ->assertSee('Awaiting');

        Livewire::test(ElectionResults::class, ['election' => $election])
            ->assertSee('1<small>/3</small>', false)
            ->assertSee('Counting')
            ->assertSee('wire:poll.15s', false)
            ->assertSee('Margin 300');
    }

    public function test_final_results_stop_refreshing_and_show_the_winner(): void
    {
        $election = $this->setUpBoard();
        [$apga] = $election->candidates->all();
        $election->recordArea($election->areas->first(), [$apga->id => 100], true);

        Livewire::actingAs($this->admin)->test(ElectionResultsManager::class, ['post' => $this->post])->call('toggleFinal');

        Livewire::test(ElectionResults::class, ['election' => $election])
            ->assertSee('Final results')
            ->assertSee('Winner')
            ->assertDontSee('wire:poll.15s', false);
    }

    public function test_editing_the_setup_keeps_votes_for_unchanged_names(): void
    {
        $election = $this->setUpBoard();
        [$apga, $apc] = $election->candidates->all();
        $awka = $election->areas->first();
        $election->recordArea($awka, [$apga->id => 500, $apc->id => 400], true);

        // Rename LP to YPP and drop Nnewi North
        Election::setUp($this->post, 'Anambra Governorship Election', 'LGA',
            ['Charles Soludo | APGA', 'Nicholas Ukachukwu | APC', 'Paul Chukwuma | YPP'],
            ['Awka South', 'Onitsha North']);

        $election = $election->fresh(['candidates', 'areas']);
        $this->assertSame(['APGA', 'APC', 'YPP'], $election->candidates->pluck('party')->all());
        $this->assertCount(2, $election->areas);
        $this->assertSame(500, $election->votesByArea()[$awka->id][$apga->id]);
        $this->assertSame('#1f8a4c', $election->candidates->first()->color, 'colour kept');
    }

    public function test_setup_needs_two_candidates_and_votes_must_be_numbers(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ElectionResultsManager::class, ['post' => $this->post])
            ->call('startSetup')
            ->set('candidatesText', 'Only One | APGA')
            ->set('areasText', 'Awka South')
            ->call('saveSetup')
            ->assertHasErrors('candidatesText');

        $election = $this->setUpBoard();
        $area = $election->areas->first();
        $candidate = $election->candidates->first();

        Livewire::actingAs($this->admin)
            ->test(ElectionResultsManager::class, ['post' => $this->post])
            ->set("votes.{$area->id}.{$candidate->id}", -5)
            ->call('saveArea', $area->id)
            ->assertHasErrors("votes.{$area->id}.{$candidate->id}");
    }

    public function test_deleting_the_board_removes_it_from_the_story(): void
    {
        $this->setUpBoard();

        Livewire::actingAs($this->admin)->test(ElectionResultsManager::class, ['post' => $this->post])->call('deleteBoard');

        $this->assertNull($this->post->election()->first());
        $this->get(route('posts.show', $this->post))->assertOk()->assertDontSee('Results by LGA');
    }

    public function test_readers_cannot_change_results(): void
    {
        $this->setUpBoard();

        Livewire::actingAs(User::factory()->create(['password' => 'password']))
            ->test(ElectionResultsManager::class, ['post' => $this->post])
            ->assertForbidden();
    }

    public function test_edit_page_links_to_live_coverage_and_results(): void
    {
        $this->actingAs($this->admin)->get(route('admin.posts.edit', $this->post->id))->assertOk()
            ->assertSee('href="#live-coverage"', false)
            ->assertSee('href="#election-results"', false)
            ->assertSee('id="election-results"', false)
            ->assertSee('id="live-coverage"', false);
    }
}
