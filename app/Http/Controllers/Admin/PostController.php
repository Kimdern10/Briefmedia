<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /* ===============================
        ACTIVE POSTS LIST
    =============================== */
    public function index()
    {
        $posts = Post::with(['category', 'admin'])
            ->latest()
            ->paginate(10);

        return view('admin.posts.index', compact('posts'));
    }

    /* ===============================
        TRASH LIST
    =============================== */
    public function trash()
    {
        $posts = Post::onlyTrashed()
            ->with(['category', 'admin'])
            ->latest()
            ->paginate(10);

        return view('admin.posts.trash', compact('posts'));
    }

    /* ===============================
        CREATE
    =============================== */
    public function create()
    {
        $categories = Category::all();
        return view('admin.posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image_1' => 'required|image|max:5120', // Increased to 5MB
            'featured_image_2' => 'nullable|image|max:5120',
            'featured_image_3' => 'nullable|image|max:5120',
            'featured_image_4' => 'nullable|image|max:5120',
            'status' => 'required|in:draft,published',
            
            // SEO validation
            'enable_auto_seo' => 'nullable|boolean',
            'meta_title' => 'nullable|max:70',
            'meta_description' => 'nullable|max:320',
            'meta_keywords' => 'nullable|max:255',
            'meta_image' => 'nullable|image|max:2048',
            'canonical_url' => 'nullable|url|max:255',
            'og_title' => 'nullable|max:70',
            'og_description' => 'nullable|max:320',
            'og_image' => 'nullable|image|max:2048',
            'twitter_title' => 'nullable|max:70',
            'twitter_description' => 'nullable|max:320',
            'twitter_image' => 'nullable|image|max:2048',
            'schema_markup' => 'nullable|json',
            'no_index' => 'nullable|boolean',
            'no_follow' => 'nullable|boolean',
        ]);

        try {
            $data = $this->preparePostData($request);
            $data['admin_id'] = Auth::id();
            
            // Handle featured images
            $data = $this->handleFeaturedImages($request, $data);
            
            // Handle SEO images
            $data = $this->handleSeoImages($request, $data);
            
            // Create the post
            $post = Post::create($data);
            
            // Log the activity
            Log::info('Post created', [
                'post_id' => $post->id,
                'title' => $post->title,
                'admin_id' => Auth::id()
            ]);

            return redirect()
                ->route('admin.posts.index')
                ->with('success', 'Post created successfully with SEO settings!');

        } catch (\Exception $e) {
            Log::error('Failed to create post: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create post. Please try again.');
        }
    }

    /* ===============================
        EDIT (WORKS FOR TRASH TOO)
    =============================== */
    public function edit($id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        $categories = Category::all();

        return view('admin.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $post = Post::withTrashed()->findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image_1' => 'nullable|image|max:5120',
            'featured_image_2' => 'nullable|image|max:5120',
            'featured_image_3' => 'nullable|image|max:5120',
            'featured_image_4' => 'nullable|image|max:5120',
            'status' => 'required|in:draft,published',
            
            // SEO validation
            'enable_auto_seo' => 'nullable|boolean',
            'meta_title' => 'nullable|max:70',
            'meta_description' => 'nullable|max:320',
            'meta_keywords' => 'nullable|max:255',
            'meta_image' => 'nullable|image|max:2048',
            'canonical_url' => 'nullable|url|max:255',
            'og_title' => 'nullable|max:70',
            'og_description' => 'nullable|max:320',
            'og_image' => 'nullable|image|max:2048',
            'twitter_title' => 'nullable|max:70',
            'twitter_description' => 'nullable|max:320',
            'twitter_image' => 'nullable|image|max:2048',
            'schema_markup' => 'nullable|json',
            'no_index' => 'nullable|boolean',
            'no_follow' => 'nullable|boolean',
        ]);

        try {
            $data = $this->preparePostData($request);
            
            // Handle featured images
            $data = $this->handleFeaturedImages($request, $data, $post);
            
            // Handle SEO images
            $data = $this->handleSeoImages($request, $data, $post);
            
            // Handle image removals
            $this->handleImageRemovals($request, $post);
            
            // Update the post
            $post->update($data);
            
            Log::info('Post updated', [
                'post_id' => $post->id,
                'title' => $post->title,
                'admin_id' => Auth::id()
            ]);

            return redirect()
                ->route('admin.posts.index')
                ->with('success', 'Post updated successfully with SEO settings!');

        } catch (\Exception $e) {
            Log::error('Failed to update post: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update post. Please try again.');
        }
    }

    /* ===============================
        SOFT DELETE
    =============================== */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        Log::info('Post moved to trash', [
            'post_id' => $post->id,
            'title' => $post->title,
            'admin_id' => Auth::id()
        ]);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post moved to trash.');
    }

    /* ===============================
        RESTORE
    =============================== */
    public function restore($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);
        $post->restore();

        Log::info('Post restored from trash', [
            'post_id' => $post->id,
            'title' => $post->title,
            'admin_id' => Auth::id()
        ]);

        return redirect()
            ->route('admin.posts.trash')
            ->with('success', 'Post restored successfully.');
    }

    /* ===============================
        FORCE DELETE
    =============================== */
    public function forceDelete($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        // Delete all associated images
        $this->deletePostImages($post);
        
        // Force delete the post
        $post->forceDelete();

        Log::info('Post permanently deleted', [
            'post_id' => $post->id,
            'title' => $post->title,
            'admin_id' => Auth::id()
        ]);

        return redirect()
            ->route('admin.posts.trash')
            ->with('success', 'Post permanently deleted.');
    }

    /* ===============================
        TOGGLE STATUS (WORKS WITH TRASH)
    =============================== */
    public function toggleStatus($id)
    {
        $post = Post::withTrashed()->findOrFail($id);

        $post->update([
            'status' => $post->status === 'published'
                ? 'draft'
                : 'published'
        ]);

        Log::info('Post status toggled', [
            'post_id' => $post->id,
            'new_status' => $post->status,
            'admin_id' => Auth::id()
        ]);

        return back()
            ->with('success', 'Post status updated successfully.');
    }

    /* ===============================
        SEO PREVIEW
    =============================== */
    public function previewSeo(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'meta_title' => 'nullable|max:70',
            'meta_description' => 'nullable|max:320',
        ]);

        // Create a temporary post instance for preview
        $post = new Post([
            'title' => $request->title,
            'content' => $request->content,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'enable_auto_seo' => $request->boolean('enable_auto_seo', true),
        ]);
        
        // Generate preview data
        $preview = [
            'google' => [
                'title' => $post->meta_title,
                'description' => $post->meta_description,
                'url' => url('/posts/' . Str::slug($request->title)),
            ],
            'facebook' => [
                'title' => $post->og_title,
                'description' => $post->og_description,
            ],
            'twitter' => [
                'title' => $post->twitter_title,
                'description' => $post->twitter_description,
            ]
        ];

        return response()->json($preview);
    }

    /* ===============================
        SEO SUGGESTIONS
    =============================== */
    public function seoSuggestions($id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        $suggestions = [];

        // Check meta title length
        $metaTitle = $post->meta_title ?? '';
        if (strlen($metaTitle) < 30) {
            $suggestions[] = [
                'type' => 'warning',
                'field' => 'meta_title',
                'message' => 'Meta title is too short. Recommended length is 50-60 characters.'
            ];
        } elseif (strlen($metaTitle) > 70) {
            $suggestions[] = [
                'type' => 'warning',
                'field' => 'meta_title',
                'message' => 'Meta title is too long. It may be truncated in search results.'
            ];
        }

        // Check meta description length
        $metaDesc = $post->meta_description ?? '';
        if (strlen($metaDesc) < 120) {
            $suggestions[] = [
                'type' => 'warning',
                'field' => 'meta_description',
                'message' => 'Meta description is too short. Recommended length is 150-160 characters.'
            ];
        } elseif (strlen($metaDesc) > 320) {
            $suggestions[] = [
                'type' => 'warning',
                'field' => 'meta_description',
                'message' => 'Meta description is too long. It may be truncated in search results.'
            ];
        }

        // Check for meta image
        if (empty($post->meta_image) && empty($post->featured_image_1)) {
            $suggestions[] = [
                'type' => 'info',
                'field' => 'meta_image',
                'message' => 'Consider adding a meta image for better social sharing.'
            ];
        }

        // Check for keywords in content
        if (!empty($post->meta_keywords)) {
            $keywords = explode(',', $post->meta_keywords);
            $content = strip_tags($post->content);
            
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (!empty($keyword) && stripos($content, $keyword) === false) {
                    $suggestions[] = [
                        'type' => 'warning',
                        'field' => 'meta_keywords',
                        'message' => "Keyword '{$keyword}' not found in content."
                    ];
                }
            }
        }

        return response()->json($suggestions);
    }

    /* ===============================
        BULK SEO UPDATE
    =============================== */
    public function bulkSeoUpdate(Request $request)
    {
        $request->validate([
            'post_ids' => 'required|array',
            'post_ids.*' => 'exists:posts,id',
            'action' => 'required|in:enable_auto_seo,disable_auto_seo,no_index,no_follow',
        ]);

        try {
            $posts = Post::whereIn('id', $request->post_ids);
            
            switch ($request->action) {
                case 'enable_auto_seo':
                    $posts->update(['enable_auto_seo' => true]);
                    $message = 'Auto SEO enabled for selected posts';
                    break;
                case 'disable_auto_seo':
                    $posts->update(['enable_auto_seo' => false]);
                    $message = 'Auto SEO disabled for selected posts';
                    break;
                case 'no_index':
                    $posts->update(['no_index' => true]);
                    $message = 'No-index set for selected posts';
                    break;
                case 'no_follow':
                    $posts->update(['no_follow' => true]);
                    $message = 'No-follow set for selected posts';
                    break;
            }

            Log::info('Bulk SEO update performed', [
                'action' => $request->action,
                'post_count' => count($request->post_ids),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk SEO update failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update SEO settings'
            ], 500);
        }
    }

    /* ===============================
        PRIVATE HELPER METHODS
    =============================== */

    /**
     * Prepare post data from request
     */
    private function preparePostData(Request $request)
    {
        $data = $request->only([
            'category_id',
            'title',
            'excerpt',
            'content',
            'status',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'canonical_url',
            'og_title',
            'og_description',
            'twitter_title',
            'twitter_description',
            'schema_markup',
        ]);

        // Generate slug from title
        $data['slug'] = Str::slug($request->title);
        
        // Ensure unique slug
        $count = Post::where('slug', 'LIKE', $data['slug'] . '%')->count();
        if ($count > 0) {
            $data['slug'] = $data['slug'] . '-' . ($count + 1);
        }
        
        // Handle boolean fields
        $data['enable_auto_seo'] = $request->boolean('enable_auto_seo', true);
        $data['no_index'] = $request->boolean('no_index', false);
        $data['no_follow'] = $request->boolean('no_follow', false);
        
        return $data;
    }

    /**
     * Handle featured images upload
     */
    private function handleFeaturedImages(Request $request, array $data, ?Post $post = null)
    {
        for ($i = 1; $i <= 4; $i++) {
            $field = "featured_image_{$i}";
            
            if ($request->hasFile($field)) {
                // Delete old image if updating
                if ($post && $post->$field) {
                    Storage::disk('public')->delete($post->$field);
                }
                
                // Store new image
                $path = $request->file($field)->store('posts/featured', 'public');
                $data[$field] = $path;
            } elseif ($post && $post->$field) {
                // Keep existing image
                $data[$field] = $post->$field;
            }
        }
        
        return $data;
    }

    /**
     * Handle SEO images upload
     */
    private function handleSeoImages(Request $request, array $data, ?Post $post = null)
    {
        $seoImages = ['meta_image', 'og_image', 'twitter_image'];
        
        foreach ($seoImages as $field) {
            if ($request->hasFile($field)) {
                // Delete old image if updating
                if ($post && $post->$field) {
                    Storage::disk('public')->delete($post->$field);
                }
                
                // Store new image
                $path = $request->file($field)->store('posts/seo', 'public');
                $data[$field] = $path;
            } elseif ($post && $post->$field) {
                // Keep existing image
                $data[$field] = $post->$field;
            }
        }
        
        return $data;
    }

    /**
     * Handle image removals
     */
    private function handleImageRemovals(Request $request, Post $post)
    {
        $imageFields = [
            'featured_image_1', 'featured_image_2', 'featured_image_3', 'featured_image_4',
            'meta_image', 'og_image', 'twitter_image'
        ];
        
        foreach ($imageFields as $field) {
            $removeField = "remove_{$field}";
            
            if ($request->has($removeField) && $post->$field) {
                Storage::disk('public')->delete($post->$field);
                $post->$field = null;
                $post->save();
            }
        }
    }

    /**
     * Delete all images associated with a post
     */
    private function deletePostImages(Post $post)
    {
        $imageFields = [
            'featured_image_1', 'featured_image_2', 'featured_image_3', 'featured_image_4',
            'meta_image', 'og_image', 'twitter_image'
        ];
        
        foreach ($imageFields as $field) {
            if ($post->$field) {
                Storage::disk('public')->delete($post->$field);
            }
        }
    }
}