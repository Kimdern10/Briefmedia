<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'post_comments';

    protected $fillable = ['post_id', 'user_id', 'content', 'parent_id', 'approved_at'];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * Comments shown to the public: approved by an admin (admins' own comments are approved instantly).
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePending($query)
    {
        return $query->whereNull('approved_at');
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    /**
     * New comments from admins go live straight away; everyone else's wait for approval.
     */
    public static function approvedAtFor(?User $author): ?Carbon
    {
        return $author && (int) $author->role_as === 1 ? now() : null;
    }

    protected static function booted(): void
    {
        // Story pages and comment counts on cards are saved for guests: new, approved or removed comments show at once
        static::saved(fn () => Post::forgetHomeCache());
        static::deleted(fn () => Post::forgetHomeCache());
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Approved replies only (what the public sees)
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->whereNotNull('approved_at')->latest();
    }

    // Every reply, approved or not (for the author's own view and the admin)
    public function allReplies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->latest();
    }
}
