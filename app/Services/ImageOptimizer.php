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

    /** Width of the small version used on story cards and lists. */
    public const CARD_WIDTH = 640;

    /**
     * Save an uploaded image on the public disk and return its path.
     * With $withCopies (cover photos), a JPEG copy for link previews and a small card
     * version are saved next to the WebP file.
     */
    public static function store(UploadedFile $file, string $directory, string $format = 'webp', int $maxWidth = self::MAX_WIDTH, bool $withJpegCopy = false): string
    {
        try {
            $image = static::load($file->getRealPath());

            if ($image === null || ($format === 'webp' && ! function_exists('imagewebp'))) {
                return $file->store($directory, 'public');
            }

            return static::saveVersions($image, $directory, $format, $maxWidth, $withJpegCopy);
        } catch (Throwable $e) {
            Log::warning('Image optimisation skipped: '.$e->getMessage());

            return $file->store($directory, 'public');
        }
    }

    /**
     * Make a compressed WebP version of a photo already on the public disk (old uploads).
     * The original file is left as it is. Returns the new path, or null if it couldn't be done.
     */
    public static function optimizeStored(string $path, bool $withCopies = false): ?string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path) || ! function_exists('imagewebp')) {
            return null;
        }

        try {
            $image = static::load($disk->path($path));

            return $image ? static::saveVersions($image, dirname($path), 'webp', self::MAX_WIDTH, $withCopies) : null;
        } catch (Throwable $e) {
            Log::warning("Could not optimise {$path}: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Add the small card version for a WebP photo that doesn't have one yet.
     */
    public static function addCardVersion(string $webpPath): bool
    {
        $disk = Storage::disk('public');
        $cardPath = static::cardPathFor($webpPath);

        if ($cardPath === null || $disk->exists($cardPath) || ! $disk->exists($webpPath)) {
            return false;
        }

        try {
            $image = static::load($disk->path($webpPath));
            if (! $image) {
                return false;
            }
            $disk->put($cardPath, static::encode(static::scaleDown($image, self::CARD_WIDTH), 'webp'));

            return true;
        } catch (Throwable $e) {
            Log::warning("Could not make a card version of {$webpPath}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Where the small card version of a WebP photo lives ("abc.webp" → "abc-640.webp").
     */
    public static function cardPathFor(?string $path): ?string
    {
        if (! $path || ! preg_match('/\.webp$/i', $path) || preg_match('/-'.self::CARD_WIDTH.'\.webp$/i', $path)) {
            return null;
        }

        return preg_replace('/\.webp$/i', '-'.self::CARD_WIDTH.'.webp', $path);
    }

    /**
     * Save the main version (and, for cover photos, the JPEG copy and card version).
     */
    private static function saveVersions(GdImage $image, string $directory, string $format, int $maxWidth, bool $withCopies): string
    {
        $disk = Storage::disk('public');
        $image = static::scaleDown($image, $maxWidth);
        $basename = trim($directory, '/').'/'.Str::random(40);
        $path = $basename.'.'.$format;

        $disk->put($path, static::encode($image, $format));

        if ($withCopies && $format === 'webp') {
            $disk->put($basename.'.jpg', static::encode(static::scaleDown($image, 1200), 'jpg'));
            $disk->put(static::cardPathFor($path), static::encode(static::scaleDown($image, self::CARD_WIDTH), 'webp'));
        }

        return $path;
    }

    /**
     * Delete a stored image and its JPEG link-preview copy and card version.
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

        if ($cardPath = static::cardPathFor($path)) {
            $disk->delete($cardPath);
        }
    }

    private static function load(string $source): ?GdImage
    {
        if (! extension_loaded('gd')) {
            return null;
        }

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
