<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * A topic such as "Soludo" or "ASUU". Posts can have several; each tag has a page at /tag/{slug}.
 */
class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    /**
     * Find or create the tags typed in the post form ("Soludo, ASUU, Super Eagles").
     *
     * @return Collection<int, self>
     */
    public static function fromList(?string $list): Collection
    {
        return collect(preg_split('/[,\n]/', (string) $list))
            ->map(fn (string $name) => Str::limit(trim(preg_replace('/\s+/', ' ', $name)), 60, ''))
            ->filter(fn (string $name) => Str::slug($name) !== '')
            ->unique(fn (string $name) => Str::slug($name))
            ->take(10)
            ->map(fn (string $name) => static::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]))
            ->values();
    }
}
