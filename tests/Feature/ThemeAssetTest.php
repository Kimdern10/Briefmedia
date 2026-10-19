<?php

namespace Tests\Feature;

use Tests\TestCase;

class ThemeAssetTest extends TestCase
{
    public function test_design_files_are_served_with_the_right_type_and_caching(): void
    {
        $css = $this->get('/theme/css/bm-site.css');
        $css->assertOk();
        $this->assertStringStartsWith('text/css', $css->headers->get('Content-Type'));
        $this->assertStringContainsString('max-age=31536000', $css->headers->get('Cache-Control'));

        $this->get('/theme/css/bm-admin.css')->assertOk();
        $this->get('/theme/css/bm-icons.css')->assertOk();
        $this->get('/theme/js/bm-site.js')->assertOk()->assertHeader('Content-Type', 'application/javascript; charset=UTF-8');
        $this->get('/theme/js/bm-admin.js')->assertOk();
        $this->get('/theme/img/bm-placeholder.svg')->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_stylesheets_are_served_minified_with_every_rule_kept(): void
    {
        $original = file_get_contents(public_path('assets/css/bm-site.css'));
        $served = file_get_contents($this->get('/theme/css/bm-site.css')->assertOk()->baseResponse->getFile()->getPathname());

        $this->assertLessThan(strlen($original) * 0.85, strlen($served));
        $this->assertStringNotContainsString('/*', $served);
        $this->assertSame(substr_count($original, '{'), substr_count($served, '{'));
        $this->assertStringContainsString('.bm-hero__slide.is-active{opacity:1;visibility:visible}', $served);
    }

    public function test_the_small_icon_stylesheet_has_every_icon_the_site_uses(): void
    {
        // Fails after a new icon is added to a view: run `php artisan icons:build` and commit bm-icons.css
        $this->artisan('icons:build', ['--check' => true])->assertSuccessful();

        $this->get(url('/'))
            ->assertSee('/theme/css/bm-icons.css?v=', false)
            ->assertDontSee('assets/css/font/bootstrap-icons.css', false);
    }

    public function test_only_the_design_files_can_be_served(): void
    {
        $this->get('/theme/css/style.css')->assertNotFound();
        $this->get('/theme/css/bm-does-not-exist.css')->assertNotFound();
        $this->get('/theme/css/bm-..%2F..%2F.env')->assertNotFound();
        $this->get('/theme/other/bm-site.css')->assertNotFound();
    }

    public function test_layouts_load_design_files_through_the_theme_route(): void
    {
        $this->get(url('/'))
            ->assertOk()
            ->assertSee('/theme/css/bm-site.css?v=', false)
            ->assertSee('/theme/js/bm-site.js?v=', false)
            ->assertDontSee('assets/css/bm-site.css', false);
    }
}
