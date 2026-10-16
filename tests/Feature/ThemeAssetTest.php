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
        $this->assertSame(public_path('assets/css/bm-site.css'), $css->baseResponse->getFile()->getPathname());

        $this->get('/theme/css/bm-admin.css')->assertOk();
        $this->get('/theme/js/bm-site.js')->assertOk()->assertHeader('Content-Type', 'application/javascript; charset=UTF-8');
        $this->get('/theme/js/bm-admin.js')->assertOk();
        $this->get('/theme/img/bm-placeholder.svg')->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
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
