<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;

class CategoryPostController extends Controller
{
    /* ===============================
     | Show posts by category
     |===============================*/
    public function category(Category $category)
    {
        $posts = Post::with([
                'category',
                'admin',
                'likedByUsers'
            ])
            ->withCount(['comments', 'likedByUsers'])
            ->where('category_id', $category->id)
            ->where('status', 'published')
            ->latest()
            ->paginate(10);

        $allCategories = Category::withCount(['posts' => function ($query) {
            $query->where('status', 'published');
        }])->get();

        $latestPosts = Post::where('status', 'published')
            ->latest()
            ->take(5)
            ->get();

        $trendingPosts = Post::where('status', 'published')
            ->orderByDesc('views')
            ->take(4)
            ->get();

        return view('user.posts.category', compact(
            'category',
            'posts',
            'allCategories',
            'latestPosts',
            'trendingPosts'
        ));
    }

    /* ===============================
     | Show single post
     |===============================*/
  public function show(Post $post)
{
    $user = Auth::user();

    if ($user) {
        // Logged in user - check database
        if (!$post->viewedByUsers()->where('user_id', $user->id)->exists()) {
            $post->increment('views');
            $post->viewedByUsers()->attach($user->id);
        }
    } else {
        // Guest user - check cookie
        $cookieName = 'viewed_post_' . $post->id;
        
        // Use request()->cookie() instead of Cookie::has()
        if (!request()->hasCookie($cookieName)) {
            $post->increment('views');
            // Queue the cookie for 30 days
            Cookie::queue($cookieName, true, 60 * 24 * 30);
        }
    }

    // Load post relationships
    $post->load([
        'category',
        'likedByUsers',
        'comments' => function ($q) {
            $q->whereNull('parent_id')
              ->with('replies.user', 'user')
              ->latest();
        }
    ])->loadCount(['comments', 'likedByUsers']);

    $relatedPosts = Post::where('status', 'published')
        ->where('category_id', $post->category_id)
        ->where('id', '!=', $post->id)
        ->latest()
        ->take(4)
        ->get();

    $latestPosts = Post::latest()->take(5)->get();

    return view('user.posts.show', compact(
        'post',
        'relatedPosts',
        'latestPosts'
    ));
}

    /* ===============================
     | Like / Unlike (Form-based - No AJAX)
     |===============================*/
   public function toggleLike(Post $post)
{
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['error' => 'Not authenticated'], 401);
    }

    if ($post->likedByUsers()->where('user_id', $user->id)->exists()) {
        $post->likedByUsers()->detach($user->id);
        $liked = false;
    } else {
        $post->likedByUsers()->attach($user->id);
        $liked = true;
    }

    return response()->json([
        'liked' => $liked,
        'likes_count' => $post->likedByUsers()->count()
    ]);
}

    /* ===============================
     | Store Comment / Reply
     |===============================*/
    public function storeComment(Request $request, Post $post)
    {
        $request->validate([
            'content'   => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:post_comments,id',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()
                ->route('login')
                ->with('error', 'Login required to comment.');
        }

        Comment::create([
            'post_id'   => $post->id,
            'user_id'   => $user->id,
            'content'   => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        return back()->with('success', 'Comment added successfully.');
    }

    /* ===============================
     | Search
     |===============================*/
    public function search(Request $request)
    {
        $query = $request->input('query');

        $posts = Post::with(['category', 'admin'])
            ->where('title', 'LIKE', "%{$query}%")
            ->orWhere('content', 'LIKE', "%{$query}%")
            ->orWhereHas('category', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->get();

        return view('user.posts.search', compact('posts', 'query'));
    }
}