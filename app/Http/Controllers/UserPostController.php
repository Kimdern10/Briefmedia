<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;

class PostController extends Controller
{
    public function category(Category $category)
    {
        $posts = Post::where('category_id', $category->id)
            ->where('status', 'published')
            ->latest()
            ->paginate(9);

        return view('user.posts.category', compact('category', 'posts'));
    }
}
