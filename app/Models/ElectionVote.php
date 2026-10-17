<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectionVote extends Model
{
    protected $fillable = ['election_area_id', 'election_candidate_id', 'votes'];

    protected $casts = ['votes' => 'integer'];
}
