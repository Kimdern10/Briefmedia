<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionArea extends Model
{
    protected $fillable = ['election_id', 'name', 'reported', 'position'];

    protected $casts = ['reported' => 'boolean'];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }
}
