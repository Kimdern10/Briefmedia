<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ElectionCandidate extends Model
{
    protected $fillable = ['election_id', 'name', 'party', 'color', 'position'];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Short name for table headings: the party, otherwise the candidate's surname.
     */
    public function shortLabel(): string
    {
        return $this->party ?: Str::afterLast($this->name, ' ');
    }
}
