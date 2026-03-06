<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\User;
use App\Models\Comment;
use App\Models\Like; // Make sure to import the Like model

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
    ];

    protected $appends = [
        'comments_count',
        'trending_score',
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

    /* ==========================
     | Accessors
     |==========================*/

    // Dynamic comment count (top-level only)
    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    // Total likes count
    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    // Trending score formula
    public function getTrendingScoreAttribute()
    {
        return $this->views
            + ($this->likes_count * 2)
            + ($this->comments_count * 3);
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
                $images[$i] = asset('storage/' . $this->$field);
            }
        }
        return $images;
    }

    /**
     * Get the best available meta title
     */
    public function getMetaTitleAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        
        if ($this->enable_auto_seo && !empty($this->title)) {
            return Str::limit($this->title, 60, '');
        }
        
        return null;
    }

    /**
     * Get the best available meta description
     */
    public function getMetaDescriptionAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        
        if ($this->enable_auto_seo && !empty($this->content)) {
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
        if (!empty($this->og_image)) {
            return asset('storage/' . $this->og_image);
        }
        
        if (!empty($this->twitter_image)) {
            return asset('storage/' . $this->twitter_image);
        }
        
        if (!empty($this->meta_image)) {
            return asset('storage/' . $this->meta_image);
        }
        
        $images = $this->featured_images;
        if (!empty($images)) {
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
        return $minutes . ' min read';
    }

    /**
     * Get the URL for the post
     */
    public function getUrlAttribute()
    {
        return url('/posts/' . $this->slug);
    }

    /**
     * Generate schema markup automatically
     */
    public function getSchemaMarkupAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        if (!$this->enable_auto_seo) {
            return null;
        }

        $authorName = $this->admin ? $this->admin->name : 'BerifMedia';
        $imageUrl = $this->social_image;

        $schema = [
            "@context" => "https://schema.org",
            "@type" => "BlogPosting",
            "headline" => $this->meta_title ?: $this->title,
            "description" => $this->meta_description,
            "datePublished" => $this->created_at?->toIso8601String(),
            "dateModified" => $this->updated_at?->toIso8601String(),
            "mainEntityOfPage" => [
                "@type" => "WebPage",
                "@id" => $this->canonical_url ?: $this->url
            ],
            "author" => [
                "@type" => "Person",
                "name" => $authorName
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => "BerifMedia",
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => asset('images/logo.png')
                ]
            ]
        ];

        if ($imageUrl) {
            $schema["image"] = [
                "@type" => "ImageObject",
                "url" => $imageUrl
            ];
        }

        if ($this->category) {
            $schema["articleSection"] = $this->category->name;
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
        if (!$this->enable_auto_seo) {
            return;
        }

        if (empty($this->meta_keywords) && !empty($this->content)) {
            $this->meta_keywords = $this->extractKeywords();
        }

        if (empty($this->og_image) && !empty($this->meta_image)) {
            $this->og_image = $this->meta_image;
        }

        if (empty($this->twitter_image) && !empty($this->og_image)) {
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
        
        $text = $this->title . ' ' . strip_tags($this->content);
        $text = strtolower($text);
        $text = preg_replace('/[^\w\s]/', ' ', $text);
        $words = str_word_count($text, 1);
        
        $words = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
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

    public function scopeTrending($query)
    {
        return $query->published()
            ->orderBy('views', 'desc')
            ->orderBy('likes', 'desc');
    }

    public function scopePopular($query)
    {
        return $query->published()
            ->orderBy('views', 'desc');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
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
            $count = static::where('slug', 'LIKE', $post->slug . '%')->count();
            if ($count > 0) {
                $post->slug = $post->slug . '-' . ($count + 1);
            }
        });

        static::saving(function ($post) {
            $post->generateSeoMetadata();
            
            if (empty($post->canonical_url) && !empty($post->slug)) {
                $post->canonical_url = $post->url;
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }
}