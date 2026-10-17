<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Userprofile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'phone', 'address', 'profile_picture'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id'); // 'user_id'
    }

    /**
     * Public URL of the uploaded profile photo, or null when there is none.
     */
    public function photoUrl(): ?string
    {
        return $this->profile_picture
            ? route('profile.photo', $this->profile_picture)
            : null;
    }
}
