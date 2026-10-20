<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Remembers which stories a guest has already been counted for, in ONE small cookie ("bm_seen"),
 * so each story counts a guest's view once every 30 days.
 *
 * It replaces the old cookie per story ("viewed_post_123", about 300 bytes each, encrypted): those
 * piled up for keen readers and were sent with every request, photos included, which slows phones
 * and can grow past what the server accepts. Old cookies a reader still has are carried over into
 * the new one (so nobody is counted twice) and removed.
 *
 * The value is a plain list such as "6e.2k-7a.2l": story id and the day it was counted, both in
 * base 36. It holds nothing secret, so it isn't encrypted (see EncryptCookies), which keeps it small.
 */
class SeenStories
{
    public const COOKIE = 'bm_seen';

    /**
     * A story counts again for the same reader after this many days.
     */
    public const DAYS = 30;

    /**
     * Most stories remembered (the oldest are dropped first): about 2 KB at most.
     */
    public const LIMIT = 300;

    private const LEGACY_PREFIX = 'viewed_post_';

    /**
     * Days since 1 January 2026, so the day fits in two or three characters.
     */
    private static function today(): int
    {
        return intdiv(now()->startOfDay()->getTimestamp() - 1767225600, 86400);
    }

    /**
     * True when this is the guest's first view of the story in the last 30 days; the cookie is
     * updated either way (old per-story cookies are folded in and removed).
     */
    public static function firstViewOf(Request $request, int $postId): bool
    {
        $today = self::today();
        $seen = self::read($request, $today);
        $migrated = false;

        foreach (array_keys($request->cookies->all()) as $name) {
            if (str_starts_with($name, self::LEGACY_PREFIX) && ctype_digit($legacyId = substr($name, strlen(self::LEGACY_PREFIX)))) {
                $seen[(int) $legacyId] ??= $today;
                Cookie::queue(Cookie::forget($name));
                $migrated = true;
            }
        }

        $isFirstView = ! isset($seen[$postId]);

        if ($isFirstView) {
            $seen[$postId] = $today;
        }

        if ($isFirstView || $migrated) {
            self::write($seen);
        }

        return $isFirstView;
    }

    /**
     * @return array<int, int> story id => day counted, only the last 30 days
     */
    private static function read(Request $request, int $today): array
    {
        $seen = [];

        foreach (explode('-', (string) $request->cookie(self::COOKIE, '')) as $entry) {
            if (! preg_match('/^([0-9a-z]+)\.([0-9a-z]+)$/', $entry, $parts)) {
                continue;
            }

            $day = (int) base_convert($parts[2], 36, 10);

            if ($today - $day < self::DAYS) {
                $seen[(int) base_convert($parts[1], 36, 10)] = $day;
            }
        }

        return $seen;
    }

    /**
     * @param  array<int, int>  $seen
     */
    private static function write(array $seen): void
    {
        asort($seen);
        $seen = array_slice($seen, -self::LIMIT, null, true);

        $value = collect($seen)
            ->map(fn (int $day, int $postId): string => base_convert((string) $postId, 10, 36).'.'.base_convert((string) $day, 10, 36))
            ->implode('-');

        Cookie::queue(self::COOKIE, $value, 60 * 24 * self::DAYS);
    }
}
