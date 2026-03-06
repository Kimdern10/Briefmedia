<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'robots_meta'
    ];
}