<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves the site's design files (public/assets/{css,js,img}/bm-*) through Laravel.
 *
 * On the Namecheap host the web root is public_html, which is separate from the project's
 * public/ folder that `git pull` updates. Serving these files from the project folder means a
 * pull is enough to ship design changes, with no manual copying into public_html.
 */
class ThemeAssetController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const CONTENT_TYPES = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
    ];

    public function __invoke(string $type, string $file): BinaryFileResponse
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $path = public_path("assets/{$type}/{$file}");

        abort_unless(isset(self::CONTENT_TYPES[$extension]) && is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => self::CONTENT_TYPES[$extension],
            // URLs carry ?v=<file modified time>, so browsers can cache them for a long time.
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
