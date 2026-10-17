<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Shrinks uploaded photos before saving them so pages load faster on mobile data:
 * turns phone photos the right way up, scales them down to $maxWidth and saves them as
 * WebP (or JPEG). Uses PHP's built-in GD, so no extra package is needed.
 *
 * If the server can't process an image (no GD/WebP support, animated GIF, out of memory),
 * the original file is saved unchanged so uploads never fail.
 */
class ImageOptimizer
{
    public const MAX_WIDTH = 1600;

    public const QUALITY = 80;

    /**
     * Save an uploaded image on the public disk and return its path.
     * With $withJpegCopy, a JPEG copy is saved next to a WebP file for link previews.
     */
    public static function store(UploadedFile $file, string $directory, string $format = 'webp', int $maxWidth = self::MAX_WIDTH, bool $withJpegCopy = false): string
    {
        try {
            $image = static::load($file);

            if ($image === null || ($format === 'webp' && ! function_exists('imagewebp'))) {
                return $file->store($directory, 'public');
            }

            $image = static::scaleDown($image, $maxWidth);
            $basename = $directory.'/'.Str::random(40);
            $path = $basename.'.'.$format;

            Storage::disk('public')->put($path, static::encode($image, $format));

            if ($withJpegCopy && $format === 'webp') {
                Storage::disk('public')->put($basename.'.jpg', static::encode(static::scaleDown($image, 1200), 'jpg'));
            }

            return $path;
        } catch (Throwable $e) {
            Log::warning('Image optimisation skipped: '.$e->getMessage());

            return $file->store($directory, 'public');
        }
    }

    /**
     * Delete a stored image and its JPEG link-preview copy.
     */
    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        $disk = Storage::disk('public');
        $disk->delete($path);

        $jpegCopy = preg_replace('/\.webp$/i', '.jpg', $path);
        if ($jpegCopy !== $path) {
            $disk->delete($jpegCopy);
        }
    }

    private static function load(UploadedFile $file): ?GdImage
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $source = $file->getRealPath();
        $type = @exif_imagetype($source) ?: null;

        // Animated GIFs would lose their animation, so keep them as they are
        if ($type === IMAGETYPE_GIF) {
            return null;
        }

        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
            default => false,
        };

        if (! $image instanceof GdImage) {
            return null;
        }

        return $type === IMAGETYPE_JPEG ? static::fixOrientation($image, $source) : $image;
    }

    /**
     * Phone cameras store sideways photos with a rotation flag; apply it so the photo shows upright.
     */
    private static function fixOrientation(GdImage $image, string $source): GdImage
    {
        $orientation = function_exists('exif_read_data') ? (int) (@exif_read_data($source)['Orientation'] ?? 1) : 1;

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        return $rotated instanceof GdImage ? $rotated : $image;
    }

    private static function scaleDown(GdImage $image, int $maxWidth): GdImage
    {
        $width = imagesx($image);

        if ($width <= $maxWidth) {
            return $image;
        }

        $scaled = imagescale($image, $maxWidth, (int) round(imagesy($image) * $maxWidth / $width), IMG_BICUBIC);

        return $scaled instanceof GdImage ? $scaled : $image;
    }

    private static function encode(GdImage $image, string $format): string
    {
        ob_start();

        if ($format === 'webp') {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            imagewebp($image, null, self::QUALITY);
        } else {
            // JPEG has no transparency: put transparent PNGs on white
            $canvas = imagecreatetruecolor(imagesx($image), imagesy($image));
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopy($canvas, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            imageinterlace($canvas, true);
            imagejpeg($canvas, null, 82);
        }

        return (string) ob_get_clean();
    }
}
