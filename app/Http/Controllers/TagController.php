<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\View\View;

/**
 * Tag pages: every published story on a topic, e.g. /tag/soludo.
 */
class TagController extends Controller
{
    public function show(Tag $tag): View
    {
        $posts = $tag->posts()
            ->where('status', 'published')
            ->with(['category', 'admin', 'likedByUsers'])
            ->latest()
            ->paginate(12);

        $relatedTags = Tag::whereHas('posts', fn ($query) => $query->where('status', 'published')
            ->whereIn('posts.id', $tag->posts()->select('posts.id')))
            ->whereKeyNot($tag->id)
            ->withCount(['posts' => fn ($query) => $query->where('status', 'published')])
            ->orderByDesc('posts_count')
            ->limit(12)
            ->get();

        return view('user.tag', compact('tag', 'posts', 'relatedTags'));
    }
}
