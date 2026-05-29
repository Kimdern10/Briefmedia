<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class PostLikeController extends Controller
{
    public function toggle(Post $post)
    {
        $user = Auth::user();

        if ($post->likedByUsers()->where('user_id', $user->id)->exists()) {
            $post->likedByUsers()->detach($user->id);
            $post->decrement('likes');

            return response()->json([
                'liked' => false,
                'likes' => $post->likes
            ]);
        }

        $post->likedByUsers()->attach($user->id);
        $post->increment('likes');

        return response()->json([
            'liked' => true,
            'likes' => $post->likes
        ]);
    }

    // Show post and increment views
    public function show(Post $post)
    {
        // Increment views
        $post->increment('views');

         // RELATED POSTS (same category, exclude current post)
    $relatedPosts = Post::where('status', 'published')
        ->where('category_id', $post->category_id)
        ->where('id', '!=', $post->id)
        ->latest()
        ->take(4)
        ->get();

    return view('user.posts.show', compact(
        'post',
        'relatedPosts'
    ));
    }

    // Like a post
    public function toggleLike(Post $post)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()
                ->route('login')
                ->with('error', 'Login required to like posts.');
        }

        if ($post->likedByUsers()->where('user_id', $user->id)->exists()) {
            $post->likedByUsers()->detach($user->id);
            $post->decrement('likes');
        } else {
            $post->likedByUsers()->attach($user->id);
            $post->increment('likes');
        }

        return back()
    }
}
