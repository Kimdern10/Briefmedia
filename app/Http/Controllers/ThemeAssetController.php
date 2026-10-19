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
     * Raise when the minifying rules change, so cached copies are made again.
     */
    private const MINIFIER_VERSION = 2;

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

        if ($extension === 'css') {
            $path = self::minifiedCopyOf($path);
        }

        return response()->file($path, [
            'Content-Type' => self::CONTENT_TYPES[$extension],
            // URLs carry ?v=<file modified time>, so browsers can cache them for a long time.
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    /**
     * The stylesheet without comments and extra spaces (about a quarter smaller), kept in
     * storage/framework/cache and made again whenever the original file changes.
     * The site layout also puts this copy of bm-site.css straight into the page (see layouts/app).
     */
    public static function minifiedCopyOf(string $path): string
    {
        $copy = storage_path('framework/cache/theme-'.md5($path).'-'.filemtime($path).'-v'.self::MINIFIER_VERSION.'.css');

        if (! is_file($copy)) {
            $css = preg_replace('~/\*.*?\*/~s', '', file_get_contents($path));
            $css = preg_replace('~\s+~', ' ', $css);
            $css = preg_replace('~\s*([{};,>])\s*~', '$1', $css);
            $css = preg_replace(['~:\s+~', '~\s+!important~'], [':', '!important'], $css);
            $css = str_replace(';}', '}', trim($css));

            file_put_contents($copy, $css, LOCK_EX);
        }

        return $copy;
    }
}
