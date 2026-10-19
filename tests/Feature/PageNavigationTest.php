<?php

namespace Tests\Feature;

use App\Models\SeoSetting;
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

        foreach (['bm-navigate.js', 'bm-site.js', 'bm-alerts.js', '/livewire/livewire'] as $file) {
            $this->assertScriptRunsOnce($html, $file);
        }

        // SweetAlert is fetched by bm-alerts.js the first time a pop-up is needed, not with every page
        $this->assertDoesNotMatchRegularExpression('~<script[^>]+sweetalert2~', $html);
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

    private function admin(): User
    {
        $admin = User::factory()->create(['password' => 'password']);
        $admin->forceFill(['role_as' => 1])->save();

        return $admin;
    }

    public function test_background_form_saves_get_the_next_page_address_instead_of_a_redirect(): void
    {
        $this->actingAs($this->admin())
            ->withHeaders(['X-BM-Background-Form' => '1'])
            ->post(route('admin.categories.store'), ['name' => 'Zz Background Category'])
            ->assertOk()
            ->assertJson(['redirect' => route('admin.categories.index')]);

        $this->assertDatabaseHas('categories', ['name' => 'Zz Background Category']);

        // The "saved" message waits in the session for the page opened next
        $this->assertSame('Category created successfully.', session('success'));
    }

    public function test_background_form_with_mistakes_goes_back_with_the_errors(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.categories.create'))
            ->withHeaders(['X-BM-Background-Form' => '1'])
            ->post(route('admin.categories.store'), ['name' => ''])
            ->assertOk()
            ->assertJson(['redirect' => route('admin.categories.create')])
            ->assertSessionHasErrors('name');
    }

    public function test_normal_form_saves_still_redirect(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), ['name' => 'Zz Normal Category'])
            ->assertRedirect(route('admin.categories.index'));
    }

    public function test_seo_settings_save_with_empty_optional_fields(): void
    {
        SeoSetting::query()->delete();

        $this->actingAs($this->admin())
            ->from(route('admin.seo.edit'))
            ->post(route('admin.seo.update'), ['site_name' => 'Zz BriefMedia', 'robots_meta' => '', 'og_type' => '', 'twitter_card' => ''])
            ->assertRedirect(route('admin.seo.edit'));

        $this->assertDatabaseHas('seo_settings', ['site_name' => 'Zz BriefMedia', 'robots_meta' => 'index, follow', 'og_type' => 'website']);
    }

    public function test_profile_and_posts_table_use_their_mobile_friendly_layouts(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.profile'))->assertOk()
            ->assertSee('ad-grid--profile', false)
            ->assertDontSee('grid-template-columns: 320px', false);

        $this->actingAs($admin)->get(route('admin.posts.index'))->assertOk()
            ->assertSee('ad-table ad-table--stack', false);
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
