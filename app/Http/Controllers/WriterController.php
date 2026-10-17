<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\View\View;

/**
 * Writer pages: clicking "Posted by …" lists every published story by that writer.
 */
class WriterController extends Controller
{
    public function show(string $slug): View
    {
        $match = Post::byWriterSlug($slug);

        abort_if($match === null, 404);

        [$name, $query] = $match;

        $posts = $query->with(['category', 'admin', 'likedByUsers'])->latest()->paginate(12);

        // Admin writers get their profile photo; typed-in writer names get initials.
        $adminWriter = User::where('name', $name)->where('role_as', 1)->first();
        $photoUrl = $adminWriter ? $adminWriter->avatarUrl(240) : 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=e4407f&color=fff&size=240';

        return view('user.writer', [
            'writerName' => $name,
            'writerPhoto' => $photoUrl,
            'posts' => $posts,
            'totalViews' => (int) (clone $query)->sum('views'),
        ]);
    }
}
