<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One timestamped update on a live coverage post (elections, matches, budget day).
 */
class LiveUpdate extends Model
{
    protected $table = 'post_live_updates';

    protected $fillable = ['post_id', 'admin_id', 'title', 'body'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
