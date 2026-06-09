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
    $user = auth()->user();
    $ip = request()->ip();

    $alreadyViewed = \App\Models\PostUserView::where('post_id', $post->id)
        ->where(function ($q) use ($user, $ip) {
            if ($user) {
                $q->where('user_id', $user->id);
            } else {
                $q->where('ip_address', $ip);
            }
        })
        ->exists();

    if (!$alreadyViewed) {
        \App\Models\PostUserView::create([
            'post_id' => $post->id,
            'user_id' => $user ? $user->id : null,
            'ip_address' => $ip,
        ]);

        $post->increment('views');
    }

    $relatedPosts = Post::where('status', 'published')
        ->where('category_id', $post->category_id)
        ->where('id', '!=', $post->id)
        ->latest()
        ->take(4)
        ->get();

    return view('user.posts.show', compact('post', 'relatedPosts'));
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
