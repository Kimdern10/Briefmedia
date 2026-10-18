<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

class SeoSetting extends Model
{
    protected $fillable = [
        'site_name',
        'default_title',
        'meta_description',
        'meta_keywords',
        'author',
        'canonical_url',

        'og_title',
        'og_description',
        'og_image',
        'og_type',

        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'twitter_site',

        'favicon',
        'site_logo',
        'google_analytics_id',
        'google_verification',
        'robots_meta',
    ];

    private const CACHE_KEY = 'seo_settings:current';

    /**
     * The site's SEO settings, cached so every page doesn't query for them.
     * Null before the table exists (fresh install) or when nothing is saved yet.
     */
    public static function current(): ?self
    {
        try {
            // false stands for "no settings saved yet", so that answer is cached too
            return Cache::rememberForever(self::CACHE_KEY, fn () => static::first() ?? false) ?: null;
        } catch (Throwable) {
            return null;
        }
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}
