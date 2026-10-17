<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves uploaded profile photos from public/Userprofile in the project folder.
 *
 * ProfileController saves uploads to public_path('Userprofile'). On the Namecheap host the
 * web root is public_html, a different folder, so photos uploaded there were never reachable
 * at /Userprofile/... Serving them through Laravel works the same locally and when hosted.
 */
class ProfilePhotoController extends Controller
{
    public function __invoke(string $file): BinaryFileResponse
    {
        $path = public_path('Userprofile/'.basename($file));

        abort_unless(is_file($path) && $this->isImage($path), 404);

        return response()->file($path, [
            // Every upload gets a new timestamped file name, so the URL never changes content.
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function isImage(string $path): bool
    {
        $mime = mime_content_type($path) ?: '';

        return in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true);
    }
}
