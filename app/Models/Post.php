<?php

namespace App\Models;

use App\Http\Middleware\CacheGuestPages;
use App\Services\ImageOptimizer;
use App\Services\PostAnnouncer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Make sure to import the Like model

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image_1',
        'featured_image_2',
        'featured_image_3',
        'featured_image_4',
        'status',
        'views',
        'likes',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'enable_auto_seo',
        'meta_image',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'schema_markup',
        'no_index',
        'no_follow',
        'headline_until',
        'breaking_until',
        'video_url',
        'author_name',
        'publish_at',
        'image_caption',
        'image_credit',
        'is_live',
        'correction_note',
        'content_updated_at',
    ];

    protected $casts = [
        'enable_auto_seo' => 'boolean',
        'no_index' => 'boolean',
        'no_follow' => 'boolean',
        'views' => 'integer',
        'likes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'trending_until' => 'datetime',
        'headline_until' => 'datetime',
        'breaking_until' => 'datetime',
        'publish_at' => 'datetime',
        'is_live' => 'boolean',
        'content_updated_at' => 'datetime',
        'announced_at' => 'datetime',
        'push_sent_at' => 'datetime',
    ];

    protected $appends = [
        'comments_count',
        'images',
        'social_image',
    ];

    /* ==========================
     | Relationships
     |==========================*/

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Admin who posted
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Users who liked this post
    public function likedByUsers()
    {
        return $this->belongsToMany(
            User::class,
            'post_user_likes',
            'post_id',
            'user_id'
        )->withTimestamps();
    }

    // Users who viewed this post
    public function viewedByUsers()
    {
        return $this->belongsToMany(User::class, 'post_user_views')
            ->withTimestamps();
    }

    // Top-level comments
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id')
            ->whereNull('parent_id')
            ->whereNotNull('approved_at') // public counts only include approved comments
            ->latest();
    }

    // All comments including replies
    public function allComments()
    {
        return $this->hasMany(Comment::class, 'post_id')->latest();
    }

    // Likes relationship
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // Topics ("Soludo", "ASUU"), each with a page at /tag/{slug}
    public function tags()
    {
        return $this->belongsToMany(Tag::class)->orderBy('name');
    }

    // Timestamped updates on a live coverage post, newest first
    public function liveUpdates()
    {
        return $this->hasMany(LiveUpdate::class)->latest()->latest('id');
    }

    // Reader poll shown inside the article
    public function poll()
    {
        return $this->hasOne(Poll::class);
    }

    // Election results board shown inside the story
    public function election()
    {
        return $this->hasOne(Election::class);
    }

    // Readers who saved this post for later
    public function bookmarkedBy()
    {
        return $this->belongsToMany(User::class, 'post_bookmarks')->withTimestamps();
    }

    /* ==========================
     | Accessors
     |==========================*/

    // Approved top-level comment count. Uses the number loaded with withCount('comments')
    // when there is one, so a page of cards doesn't run one query per card.
    public function getCommentsCountAttribute()
    {
        if (array_key_exists('comments_count', $this->attributes)) {
            return (int) $this->attributes['comments_count'];
        }

        return $this->attributes['comments_count'] = $this->comments()->count();
    }

    // Total likes count
    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    // Featured images array
    public function getImagesAttribute()
    {
        return array_filter([
            $this->featured_image_1,
            $this->featured_image_2,
            $this->featured_image_3,
            $this->featured_image_4,
        ]);
    }

    // Get featured images with full URLs
    public function getFeaturedImagesAttribute()
    {
        $images = [];
        for ($i = 1; $i <= 4; $i++) {
            $field = "featured_image_{$i}";
            if ($this->$field) {
                $images[$i] = static::photoUrl($this->$field);
            }
        }

        return $images;
    }

    /**
     * Get the best available meta title
     */
    public function getMetaTitleAttribute($value)
    {
        if (! empty($value)) {
            return $value;
        }

        if ($this->enable_auto_seo && ! empty($this->title)) {
            return Str::limit($this->title, 60, '');
        }

        return null;
    }

    /**
     * Get the best available meta description
     */
    public function getMetaDescriptionAttribute($value)
    {
        if (! empty($value)) {
            return $value;
        }

        if ($this->enable_auto_seo && ! empty($this->content)) {
            $cleanContent = strip_tags($this->content);
            $cleanContent = preg_replace('/\s+/', ' ', $cleanContent);
            $cleanContent = trim($cleanContent);

            return Str::limit($cleanContent, 160, '...');
        }

        return null;
    }

    /**
     * Get the best available Open Graph title
     */
    public function getOgTitleAttribute($value)
    {
        return $value ?: $this->meta_title;
    }

    /**
     * Get the best available Open Graph description
     */
    public function getOgDescriptionAttribute($value)
    {
        return $value ?: $this->meta_description;
    }

    /**
     * Get the best available Twitter title
     */
    public function getTwitterTitleAttribute($value)
    {
        return $value ?: $this->og_title;
    }

    /**
     * Get the best available Twitter description
     */
    public function getTwitterDescriptionAttribute($value)
    {
        return $value ?: $this->og_description;
    }

    /**
     * Get the first available image for social sharing
     */
    public function getSocialImageAttribute()
    {
        if (! empty($this->og_image)) {
            return asset('storage/'.$this->og_image);
        }

        if (! empty($this->twitter_image)) {
            return asset('storage/'.$this->twitter_image);
        }

        if (! empty($this->meta_image)) {
            return asset('storage/'.$this->meta_image);
        }

        $images = $this->featured_images;
        if (! empty($images)) {
            return $images[1]; // Return first featured image
        }

        return asset('images/default-social-image.jpg');
    }

    /**
     * Get reading time in minutes
     */
    public function getReadingTimeAttribute()
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = ceil($wordCount / 200); // Average reading speed

        return $minutes.' min read';
    }

    /**
     * Get the URL for the post
     */
    public function getUrlAttribute()
    {
        return url('/posts/'.$this->slug);
    }

    /**
     * Generate schema markup automatically
     */
    public function getSchemaMarkupAttribute($value)
    {
        if (! empty($value)) {
            return $value;
        }

        if (! $this->enable_auto_seo) {
            return null;
        }

        $authorName = $this->admin ? $this->admin->name : 'BriefMedia';
        $imageUrl = $this->social_image;

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $this->meta_title ?: $this->title,
            'description' => $this->meta_description,
            'datePublished' => $this->created_at?->toIso8601String(),
            'dateModified' => $this->updated_at?->toIso8601String(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->canonical_url ?: $this->url,
            ],
            'author' => [
                '@type' => 'Person',
                'name' => $authorName,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'BriefMedia',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo.png'),
                ],
            ],
        ];

        if ($imageUrl) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => $imageUrl,
            ];
        }

        if ($this->category) {
            $schema['articleSection'] = $this->category->name;
        }

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /* ==========================
     | Methods
     |==========================*/

    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function isViewedBy(User $user): bool
    {
        return $this->viewedByUsers()->where('user_id', $user->id)->exists();
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    /**
     * Auto-generate SEO metadata before saving
     */
    public function generateSeoMetadata(): void
    {
        if (! $this->enable_auto_seo) {
            return;
        }

        if (empty($this->meta_keywords) && ! empty($this->content)) {
            $this->meta_keywords = $this->extractKeywords();
        }

        if (empty($this->og_image) && ! empty($this->meta_image)) {
            $this->og_image = $this->meta_image;
        }

        if (empty($this->twitter_image) && ! empty($this->og_image)) {
            $this->twitter_image = $this->og_image;
        }
    }

    /**
     * Extract keywords from content
     */
    private function extractKeywords(): ?string
    {
        $stopWords = ['the', 'and', 'for', 'that', 'this', 'with', 'from', 'have', 'are', 'was',
            'were', 'has', 'had', 'but', 'not', 'what', 'all', 'when', 'can', 'said',
            'there', 'use', 'an', 'each', 'which', 'do', 'how', 'their', 'will', 'other',
            'about', 'out', 'many', 'then', 'them', 'these', 'some', 'her', 'would',
            'make', 'like', 'him', 'into', 'time', 'has', 'look', 'two', 'more', 'write',
            'go', 'see', 'number', 'way', 'could', 'people', 'than', 'first', 'been',
            'call', 'who', 'its', 'now', 'find', 'long', 'down', 'day', 'did', 'get',
            'come', 'made', 'may', 'part'];

        $text = $this->title.' '.strip_tags($this->content);
        $text = strtolower($text);
        $text = preg_replace('/[^\w\s]/', ' ', $text);
        $words = str_word_count($text, 1);

        $words = array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 3 && ! in_array($word, $stopWords);
        });

        if (empty($words)) {
            return null;
        }

        $wordCounts = array_count_values($words);
        arsort($wordCounts);
        $keywords = array_slice(array_keys($wordCounts), 0, 10);

        return implode(', ', $keywords);
    }

    /* ==========================
     | Route Model Binding
     |==========================*/

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /* ==========================
     | Scopes
     |==========================*/

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeWithSeo($query)
    {
        return $query->where('no_index', false);
    }

    public function scopeWithoutSeo($query)
    {
        return $query->where('no_index', true);
    }

    /**
     * Cache keys for the homepage ("home:home") and the header/footer menus and ticker ("home:nav").
     */
    public const HOME_CACHE_PREFIX = 'home:';

    /**
     * Clear the cached homepage and menus so changes show straight away.
     */
    public static function forgetHomeCache(): void
    {
        Cache::forget(self::HOME_CACHE_PREFIX.'home');
        Cache::forget(self::HOME_CACHE_PREFIX.'nav');
        Cache::forget(self::HOME_CACHE_PREFIX.'years');
        // Finished pages kept for guests (CacheGuestPages): a new version number retires every saved copy
        Cache::forever(CacheGuestPages::VERSION_KEY, (int) Cache::get(CacheGuestPages::VERSION_KEY, 1) + 1);
    }

    /**
     * Longest summary (excerpt) allowed in the post editor, in characters.
     */
    public const EXCERPT_MAX = 2000;

    /**
     * How long an admin's "Trend" pick lasts.
     */
    public const PINNED_TRENDING_DAYS = 2;

    /**
     * How long a post stays "breaking news" after the admin ticks the box. Kept short:
     * "breaking" means happening now, and a red bar left up all day gets ignored.
     */
    public const BREAKING_HOURS = 6;

    /**
     * How long a post stays a headline after the admin ticks the box.
     */
    public const HEADLINE_HOURS = 24;

    /**
     * Scheduled post: saved as a draft with a publish_at time; it goes live automatically.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'draft' && $this->publish_at !== null;
    }

    /**
     * Publish every scheduled post whose time has come. Returns how many went live.
     * Runs from the `posts:publish-scheduled` command (cron) and on page visits, so
     * scheduling works even without a cron job.
     */
    public static function publishDueScheduled(): int
    {
        $due = static::where('status', 'draft')
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', now())
            ->get();

        foreach ($due as $post) {
            $publishAt = $post->publish_at;

            $post->forceFill([
                'status' => 'published',
                'publish_at' => null,
                // Sort it as a fresh story from the moment it went live
                'created_at' => $publishAt,
            ])->save();

            Log::info('Scheduled post published', ['post_id' => $post->id, 'title' => $post->title]);
        }

        return $due->count();
    }

    /**
     * Headline news: shown first in the homepage slider until headline_until.
     */
    public function isHeadline(): bool
    {
        return $this->status === 'published'
            && $this->headline_until !== null
            && $this->headline_until->isFuture();
    }

    /**
     * Breaking news: shown in the red ticker and with a red badge until breaking_until.
     */
    public function isBreaking(): bool
    {
        return $this->status === 'published'
            && $this->breaking_until !== null
            && $this->breaking_until->isFuture();
    }

    /**
     * Live coverage post: shows a LIVE badge and its timestamped updates refresh by themselves.
     */
    public function isLive(): bool
    {
        return $this->status === 'published' && (bool) $this->is_live;
    }

    /**
     * Whether the story was edited after it went live (shows "Updated …" on the article).
     */
    public function wasUpdated(): bool
    {
        return $this->content_updated_at !== null
            && $this->created_at !== null
            && $this->content_updated_at->greaterThan($this->created_at->copy()->addMinutes(5));
    }

    /**
     * When the story last really changed: its edit time, the newest live update, or when it went live.
     */
    public function lastModifiedAt(): Carbon
    {
        $times = collect([$this->created_at, $this->content_updated_at]);

        if ($this->is_live) {
            $times->push($this->liveUpdates()->value('created_at'));
        }

        return Carbon::parse($times->filter()->max());
    }

    /**
     * Replace the post's tags with the ones typed in the form ("Soludo, ASUU").
     */
    public function syncTags(?string $list): void
    {
        $this->tags()->sync(Tag::fromList($list)->pluck('id'));
    }

    /**
     * The post's tags as the comma-separated text shown in the form.
     */
    public function tagList(): string
    {
        return $this->tags->pluck('name')->implode(', ');
    }

    /**
     * Web address of an uploaded photo shown on the site. With IMAGE_CDN_URL set (e.g. a Bunny CDN
     * pull zone that copies from this site), photos come from the CDN, which keeps copies near
     * readers; without it they load from this site as before.
     */
    public static function photoUrl(string $path): string
    {
        $cdn = config('services.image_cdn.url');

        return $cdn ? rtrim($cdn, '/').'/storage/'.ltrim($path, '/') : asset('storage/'.$path);
    }

    /**
     * Cover photo for the site: "card" is the small 640px version used on story cards and lists
     * (falls back to the full photo when an old upload hasn't been optimised yet).
     */
    public function coverUrl(string $size = 'full'): string
    {
        $path = $this->featured_image_1;

        if (! $path) {
            return route('theme.asset', ['img', 'bm-placeholder.svg']);
        }

        if ($size === 'card' && ($cardPath = ImageOptimizer::cardPathFor($path))
            && Storage::disk('public')->exists($cardPath)) {
            return static::photoUrl($cardPath);
        }

        return static::photoUrl($path);
    }

    /**
     * "srcset" for the cover photo: the 640px card version and the full photo, so phones download
     * the small one and big screens the big one. Null when there's no card version yet.
     */
    public function coverSrcset(): ?string
    {
        $path = $this->featured_image_1;
        $cardPath = ImageOptimizer::cardPathFor($path);

        if (! $cardPath || ! Storage::disk('public')->exists($cardPath)) {
            return null;
        }

        $fullWidth = ImageOptimizer::widthOf($path) ?? ImageOptimizer::MAX_WIDTH;
        $cardWidth = min(ImageOptimizer::CARD_WIDTH, $fullWidth);

        return static::photoUrl($cardPath).' '.$cardWidth.'w, '.static::photoUrl($path).' '.$fullWidth.'w';
    }

    /**
     * Image for Facebook/WhatsApp/X link previews. Uploaded cover photos are saved as WebP for
     * speed, with a JPEG copy next to them because some apps don't preview WebP.
     */
    public function shareImageUrl(): string
    {
        $path = $this->featured_image_1;

        if (! $path) {
            return url('images/default-social-image.jpg');
        }

        $jpegCopy = preg_replace('/\.webp$/i', '.jpg', $path);

        if ($jpegCopy !== $path && Storage::disk('public')->exists($jpegCopy)) {
            return url('storage/'.$jpegCopy);
        }

        return url('storage/'.$path);
    }

    /**
     * Width and height of the link-preview image. Facebook only shows the photo on the very
     * first share when the page states its size.
     *
     * @return array{0: int, 1: int}|null
     */
    public function shareImageSize(): ?array
    {
        if (! $this->featured_image_1) {
            return null;
        }

        $disk = Storage::disk('public');
        $jpegCopy = preg_replace('/\.webp$/i', '.jpg', $this->featured_image_1);
        $path = $disk->exists($jpegCopy) ? $jpegCopy : $this->featured_image_1;

        if (! $disk->exists($path)) {
            return null;
        }

        $size = @getimagesize($disk->path($path));

        return $size ? [(int) $size[0], (int) $size[1]] : null;
    }

    /**
     * Cover photo for emails (full web address, JPEG copy when there is one), or null.
     */
    public function emailImageUrl(): ?string
    {
        return $this->featured_image_1 ? $this->shareImageUrl() : null;
    }

    /**
     * Schema markup an admin typed in the SEO tab, or null. The post form used to save the
     * automatic BlogPosting schema back into the post; those copies are ignored so every
     * story gets the newer NewsArticle schema.
     */
    public function customSchemaMarkup(): ?string
    {
        $stored = trim((string) $this->getRawOriginal('schema_markup'));

        if ($stored === '' || (json_decode($stored, true)['@type'] ?? null) === 'BlogPosting') {
            return null;
        }

        return $stored;
    }

    /**
     * Google News structured data: NewsArticle, or LiveBlogPosting for live coverage.
     *
     * @return array<string, mixed>
     */
    public function newsArticleSchema(): array
    {
        $images = collect([$this->featured_image_1, $this->featured_image_2, $this->featured_image_3])
            ->filter()
            ->map(fn (string $path) => url('storage/'.$path))
            ->values()
            ->all();

        $siteName = optional(SeoSetting::current())->site_name ?: config('app.name', 'BriefMedia');
        $logo = optional(SeoSetting::current())->site_logo;

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $this->isLive() ? 'LiveBlogPosting' : 'NewsArticle',
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('posts.show', $this)],
            'headline' => Str::limit($this->title, 110, ''),
            'description' => Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($this->excerpt ?: $this->content))), 200),
            'image' => $images ?: [url('images/default-social-image.jpg')],
            'datePublished' => $this->created_at?->toIso8601String(),
            'dateModified' => $this->lastModifiedAt()->toIso8601String(),
            'author' => [[
                '@type' => 'Person',
                'name' => $this->authorDisplayName(),
                'url' => $this->authorUrl(),
            ]],
            'publisher' => array_filter([
                '@type' => 'NewsMediaOrganization',
                'name' => $siteName,
                'url' => url('/'),
                'logo' => $logo ? ['@type' => 'ImageObject', 'url' => url('storage/'.$logo)] : null,
            ]),
            'isAccessibleForFree' => true,
        ];

        if ($this->category) {
            $schema['articleSection'] = $this->category->name;
        }

        if ($this->tags->isNotEmpty()) {
            $schema['keywords'] = $this->tags->pluck('name')->all();
        }

        if ($this->isLive()) {
            $schema['coverageStartTime'] = $this->created_at?->toIso8601String();
            $schema['liveBlogUpdate'] = $this->liveUpdates()->take(10)->get()->map(fn (LiveUpdate $update) => [
                '@type' => 'BlogPosting',
                'headline' => Str::limit($update->title ?: strip_tags($update->body), 110, ''),
                'datePublished' => $update->created_at->toIso8601String(),
                'articleBody' => Str::limit(strip_tags($update->body), 500),
            ])->all();
        }

        return $schema;
    }

    /**
     * Article body with even paragraph spacing: plain text is split into
     * paragraphs, and the empty paragraphs and double line breaks editors
     * leave behind (which show as big gaps) are removed.
     */
    public function formattedContent(): string
    {
        $html = trim((string) $this->content);

        if ($html === '') {
            return '';
        }

        // Plain text (no block tags): blank lines start a new paragraph, single lines become <br>
        if (! preg_match('~<(p|div|h[1-6]|ul|ol|blockquote|table|figure|img|iframe)\b~i', $html)) {
            return collect(preg_split('~\R\s*\R~', $html))
                ->map(fn (string $paragraph) => trim($paragraph))
                ->filter()
                ->map(fn (string $paragraph) => '<p>'.nl2br($paragraph, false).'</p>')
                ->implode("\n");
        }

        $spacer = '(?:\s|&nbsp;|&#160;|\x{00A0}|<br\s*/?>)';

        // Two or more <br> in a row inside a paragraph: split it into two paragraphs
        $html = preg_replace_callback('~(<p\b[^>]*>)(.*?)</p>~isu', fn (array $match) => $match[1]
            .preg_replace('~(?:<br\s*/?>\s*(?:&nbsp;|\x{00A0})?\s*){2,}~iu', '</p><p>', $match[2]).'</p>', $html);

        // Empty paragraphs/divs (only spaces, &nbsp; or <br>)
        $html = preg_replace('~<(p|div)\b[^>]*>'.$spacer.'*</\1>~iu', '', $html);

        // A <br> right at the start or end of a paragraph
        $html = preg_replace(['~(<p\b[^>]*>)\s*<br\s*/?>~i', '~<br\s*/?>\s*</p>~i'], ['$1', '</p>'], $html);

        return trim($html);
    }

    /**
     * Name shown after "Posted by": the writer's name typed in the post form,
     * otherwise the admin who posted it.
     */
    public function authorDisplayName(): string
    {
        return trim((string) $this->author_name) !== ''
            ? trim($this->author_name)
            : (optional($this->admin)->name ?? 'BriefMedia');
    }

    /**
     * Address of the writer page for this post's author ("Posted by" link).
     */
    public function authorUrl(): string
    {
        return route('writer.show', Str::slug($this->authorDisplayName()) ?: 'briefmedia');
    }

    /**
     * Published posts by the writer whose name turns into $slug, plus that writer's name.
     * Matches the "Written by" name, or the posting admin's name when no writer was typed in.
     *
     * @return array{0: string, 1: Builder}|null
     */
    public static function byWriterSlug(string $slug): ?array
    {
        $typedNames = static::query()->whereNotNull('author_name')->where('author_name', '!=', '')
            ->distinct()->pluck('author_name');
        $adminNames = User::whereIn('id', static::query()->select('admin_id'))->pluck('name', 'id');

        $name = $typedNames->concat($adminNames->values())
            ->first(fn ($candidate) => Str::slug((string) $candidate) === $slug);

        if ($name === null) {
            return null;
        }

        $adminIds = $adminNames->filter(fn ($adminName) => $adminName === $name)->keys();

        $query = static::query()
            ->where('status', 'published')
            ->where(function ($query) use ($name, $adminIds) {
                $query->where('author_name', $name)
                    ->orWhere(function ($query) use ($adminIds) {
                        $query->where(fn ($q) => $q->whereNull('author_name')->orWhere('author_name', ''))
                            ->whereIn('admin_id', $adminIds);
                    });
            });

        return [$name, $query];
    }

    /**
     * Years that have published stories, newest first (for "search by date").
     *
     * @return array<int, int>
     */
    public static function publishedYears(): array
    {
        return Cache::remember(self::HOME_CACHE_PREFIX.'years', 3600, fn () => static::query()
            ->where('status', 'published')
            ->whereNotNull('created_at')
            ->selectRaw('DISTINCT YEAR(created_at) as year')
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->all());
    }

    /**
     * Count one reader view: the total on the post and today's count. Done straight in the
     * database so the post's "last modified" time (used by the sitemap) stays the same.
     */
    public function countView(): void
    {
        DB::table('posts')->where('id', $this->id)->increment('views');
        $this->views = (int) $this->views + 1;
        $this->syncOriginalAttribute('views');

        static::recordDailyView($this->id);
    }

    /**
     * Count one view for today (for "Most read today").
     */
    public static function recordDailyView(int $postId): void
    {
        DB::table('post_daily_views')->upsert(
            [['post_id' => $postId, 'date' => now()->toDateString(), 'views' => 1]],
            ['post_id', 'date'],
            ['views' => DB::raw('views + 1')]
        );
    }

    /**
     * Most viewed published stories today. Early in the day (fewer than $minimum with views),
     * yesterday's views are added in so the list isn't empty.
     *
     * @return Collection<int, self>
     */
    public static function mostReadToday(int $limit = 5, int $minimum = 3): Collection
    {
        foreach ([[now()->toDateString()], [now()->subDay()->toDateString(), now()->toDateString()]] as $dates) {
            $totals = DB::table('post_daily_views')
                ->whereIn('date', $dates)
                ->groupBy('post_id')
                ->selectRaw('post_id, SUM(views) as total')
                ->orderByDesc('total')
                ->limit($limit * 2)
                ->pluck('total', 'post_id');

            $posts = static::with('category')
                ->where('status', 'published')
                ->whereIn('id', $totals->keys())
                ->get()
                ->each(fn (self $post) => $post->views_today = (int) $totals[$post->id])
                ->sortByDesc('views_today')
                ->values()
                ->take($limit);

            if ($posts->count() >= $minimum || count($dates) === 2) {
                return $posts;
            }
        }

        return collect();
    }

    /**
     * Safe embed address for the post's video link (YouTube, Vimeo or Facebook), or null.
     * Only the video ID is taken from the link, so arbitrary pages can't be embedded.
     */
    public function videoEmbedUrl(): ?string
    {
        return static::embedUrlFor($this->video_url);
    }

    public static function embedUrlFor(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = strtolower(preg_replace('/^(www\.|m\.)/', '', (string) parse_url($url, PHP_URL_HOST)));
        $path = (string) parse_url($url, PHP_URL_PATH);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        if (in_array($host, ['youtube.com', 'youtu.be', 'youtube-nocookie.com'], true)) {
            $id = match (true) {
                $host === 'youtu.be' => trim($path, '/'),
                str_starts_with($path, '/shorts/'), str_starts_with($path, '/embed/'), str_starts_with($path, '/live/') => explode('/', trim($path, '/'))[1] ?? '',
                default => $query['v'] ?? '',
            };

            return preg_match('/^[A-Za-z0-9_-]{6,20}$/', $id) ? "https://www.youtube-nocookie.com/embed/{$id}" : null;
        }

        if (in_array($host, ['vimeo.com', 'player.vimeo.com'], true) && preg_match('#/(\d{5,12})(?:$|[/?])#', $path.'/', $match)) {
            return "https://player.vimeo.com/video/{$match[1]}";
        }

        if (in_array($host, ['facebook.com', 'fb.watch'], true)) {
            return 'https://www.facebook.com/plugins/video.php?show_text=false&href='.rawurlencode($url);
        }

        return null;
    }

    /**
     * Whether an admin has marked this post as trending and the pick hasn't expired.
     */
    public function isPinnedTrending(): bool
    {
        return $this->trending_until !== null && $this->trending_until->isFuture();
    }

    /**
     * Trending stories used by the homepage, category sidebar, admin dashboard and newsletter.
     *
     * Only posts an admin marked with the 🔥 Trend button show, for PINNED_TRENDING_DAYS
     * days, newest pick first. Nothing trends automatically.
     *
     * Each post gets `trend_likes`, `trend_comments`, `trend_score`
     * (views + likes x 2 + comments x 3) and `trend_pinned` attributes.
     *
     * @return Collection<int, self>
     */
    public static function trendingNow(int $limit = 8): Collection
    {
        return static::query()
            ->with(['category', 'admin'])
            ->withCount([
                'likedByUsers as trend_likes',
                'comments as trend_comments',
            ])
            ->where('status', 'published')
            ->where('trending_until', '>', now())
            ->orderByDesc('trending_until')
            ->take($limit)
            ->get()
            ->each(function (self $post): void {
                $post->trend_pinned = true;
                $post->trend_score = (int) $post->views
                    + ((int) $post->trend_likes * 2)
                    + ((int) $post->trend_comments * 3);
            });
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
                ->orWhere('content', 'LIKE', "%{$term}%")
                ->orWhere('excerpt', 'LIKE', "%{$term}%");
        });
    }

    /* ==========================
     | Boot
     |==========================*/

    protected static function booted()
    {
        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }

            // Ensure unique slug
            $count = static::where('slug', 'LIKE', $post->slug.'%')->count();
            if ($count > 0) {
                $post->slug = $post->slug.'-'.($count + 1);
            }
        });

        static::saving(function ($post) {
            $post->generateSeoMetadata();

            if (empty($post->canonical_url) && ! empty($post->slug)) {
                $post->canonical_url = $post->url;
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }

            // "Updated" label: an already-published story whose words changed
            if ($post->getOriginal('status') === 'published' && $post->isDirty(['title', 'excerpt', 'content'])) {
                $post->content_updated_at = now();
            }
        });

        // Share newly published posts to Facebook/Telegram, and send breaking news push alerts
        static::saved(fn (self $post) => PostAnnouncer::handle($post));

        // Show changes on the homepage straight away instead of after the 1-minute cache
        static::saved(fn () => static::forgetHomeCache());
        static::deleted(fn () => static::forgetHomeCache());
        static::restored(fn () => static::forgetHomeCache());
    }
}
