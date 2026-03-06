<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    // Specify the table name explicitly
    protected $table = 'post_comments';

    protected $fillable = ['post_id', 'user_id', 'content', 'parent_id'];

    // Relationship to the post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Relationship to the user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Replies (children)
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->latest();
    }

    
}
