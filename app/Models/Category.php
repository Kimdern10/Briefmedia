<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description',
    ];

    /**
     * Boot method to automatically generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);
        });

        static::updating(function ($category) {
            $category->slug = Str::slug($category->name);
        });

        // Menus and homepage sections are cached for a minute; show category changes at once
        static::saved(fn () => Post::forgetHomeCache());
        static::deleted(fn () => Post::forgetHomeCache());
    }

    /**
     * Relationship: Category has many posts
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
