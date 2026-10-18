<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Pages swap in without a full reload (Livewire navigate, see bm-navigate.js). The shared
 * scripts must be marked data-navigate-once, or they would run again on every page and
 * double up click handlers.
 */
class PageNavigationTest extends TestCase
{
    use DatabaseTransactions;

    private function assertScriptRunsOnce(string $html, string $file): void
    {
        $this->assertMatchesRegularExpression('~<script[^>]+'.preg_quote($file, '~').'[^>]*data-navigate-once~', $html, "{$file} must run once");
    }

    public function test_public_pages_load_the_navigation_script_once(): void
    {
        $html = $this->get(url('/'))->assertOk()->getContent();

        foreach (['bm-navigate.js', 'bm-site.js', 'sweetalert2', 'bm-alerts.js', '/livewire/livewire'] as $file) {
            $this->assertScriptRunsOnce($html, $file);
        }
    }

    public function test_admin_pages_load_the_navigation_script_once(): void
    {
        $admin = User::factory()->create(['password' => 'password']);
        $admin->forceFill(['role_as' => 1])->save();

        $html = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->getContent();

        foreach (['bm-navigate.js', 'bm-admin.js', 'sweetalert2', 'bm-alerts.js', '/livewire/livewire'] as $file) {
            $this->assertScriptRunsOnce($html, $file);
        }

        // Charts wait for Chart.js instead of loading it with a separate script tag each visit
        $this->assertStringContainsString("BM.loadScript('https://cdn.jsdelivr.net/npm/chart.js", $html);
        $this->assertStringNotContainsString('<script src="https://cdn.jsdelivr.net/npm/chart.js', $html);
    }

    public function test_post_editor_waits_for_ckeditor_and_warns_about_unsaved_changes(): void
    {
        $admin = User::factory()->create(['password' => 'password']);
        $admin->forceFill(['role_as' => 1])->save();

        $this->actingAs($admin)->get(route('admin.posts.create'))->assertOk()
            ->assertSee("BM.loadScript('https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js')", false)
            ->assertSee("addEventListener('livewire:navigate', onLeave)", false)
            ->assertDontSee('<script src="https://cdn.ckeditor.com', false);
    }

    public function test_navigation_script_is_served(): void
    {
        $this->get(route('theme.asset', ['js', 'bm-navigate.js']))->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=UTF-8');

        $script = file_get_contents(public_path('assets/js/bm-navigate.js'));
        $this->assertStringContainsString('Livewire.navigate', $script);
        $this->assertStringContainsString('livewire:navigated', $script);
    }
}
