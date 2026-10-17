<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes; // 👈 ADD THIS
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes; // 👈 ADD THIS

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Soft delete date column
     */
    protected $dates = ['deleted_at']; // 👈 ADD THIS

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function Userprofile(): HasOne
    {
        return $this->hasOne(Userprofile::class);
    }

    /**
     * Profile photo URL, or a coloured initial avatar when no photo has been uploaded.
     */
    public function avatarUrl(int $size = 120): string
    {
        return optional($this->Userprofile)->photoUrl()
            ?? 'https://ui-avatars.com/api/?name='.urlencode((string) $this->name).'&background=e4407f&color=fff&size='.$size;
    }

    public function likedPosts()
    {
        return $this->belongsToMany(
            Post::class,
            'post_user_likes',
            'user_id',
            'post_id'
        )->withTimestamps();
    }

    /**
     * Stories this reader saved for later, most recently saved first.
     */
    public function bookmarks()
    {
        return $this->belongsToMany(Post::class, 'post_bookmarks')
            ->withTimestamps()
            ->orderByPivot('created_at', 'desc');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}
