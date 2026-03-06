<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasFactory;

    // Mass assignable fields
    protected $fillable = [
        'email',
        'unsubscribe_token',
        'is_active',
    ];

    // Optional: cast is_active to boolean
    protected $casts = [
        'is_active' => 'boolean',
    ];
}