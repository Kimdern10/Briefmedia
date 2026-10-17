<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollOption extends Model
{
    protected $fillable = ['poll_id', 'label', 'votes', 'position'];

    protected $casts = ['votes' => 'integer', 'position' => 'integer'];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }
}
