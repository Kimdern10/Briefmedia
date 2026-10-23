<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Ad or sponsor code for one place on the site, managed in Admin → Ads.
 */
class AdSlot extends Model
{
    protected $fillable = ['placement', 'code', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /**
     * Every place an ad can go, with the label and hint shown in the admin.
     *
     * @var array<string, array{label: string, hint: string}>
     */
    public const PLACEMENTS = [
        'article_top' => ['label' => 'Article: under the photo', 'hint' => 'Shown under the cover photo, before the story starts.'],
        'article_middle' => ['label' => 'Article: in the middle', 'hint' => 'Shown after the 3rd paragraph of the story.'],
        'article_bottom' => ['label' => 'Article: end of story', 'hint' => 'Shown after the story, before the comments.'],
        'sidebar' => ['label' => 'Sidebar', 'hint' => 'Top of the sidebar on article and category pages.'],
        'home_between' => ['label' => 'Homepage: between sections', 'hint' => 'Shown on the homepage after the second category section.'],
    ];

    private const CACHE_KEY = 'ad_slots:active';

    /**
     * Active code for a placement, or null. Cached so pages don't hit the database for ads.
     */
    public static function codeFor(string $placement): ?string
    {
        $active = Cache::rememberForever(self::CACHE_KEY, function () {
            if (! Schema::hasTable('ad_slots')) {
                return [];
            }

            return static::where('is_active', true)->whereNotNull('code')->pluck('code', 'placement')->all();
        });

        $code = trim((string) ($active[$placement] ?? ''));

        return $code === '' ? null : $code;
    }

    /**
     * Put $insert after the $afterParagraph-th paragraph of an article body.
     * Short stories (fewer paragraphs) are returned unchanged.
     */
    public static function insertAfterParagraph(string $html, string $insert, int $afterParagraph = 3): string
    {
        $offset = 0;

        for ($i = 0; $i < $afterParagraph; $i++) {
            $position = stripos($html, '</p>', $offset);

            if ($position === false) {
                return $html;
            }

            $offset = $position + 4;
        }

        // Don't put the ad right at the very end of the story
        if (trim(substr($html, $offset)) === '') {
            return $html;
        }

        return substr($html, 0, $offset).$insert.substr($html, $offset);
    }

    /**
     * Put several blocks inside a story, each after the given paragraph (blocks for a paragraph the
     * story doesn't have, or for its very end, go after the text instead).
     *
     * @param  array<int, string>  $blocks  paragraph number => HTML
     */
    public static function insertAfterParagraphs(string $html, array $blocks): string
    {
        $atTheEnd = '';
        krsort($blocks);

        // From the last paragraph back, so earlier positions don't move
        foreach ($blocks as $afterParagraph => $block) {
            $withBlock = self::insertAfterParagraph($html, $block, $afterParagraph);

            if ($withBlock === $html) {
                $atTheEnd = $block.$atTheEnd;
            }

            $html = $withBlock;
        }

        return $html.$atTheEnd;
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }
}
