{{-- resources/views/user/partials/post-search-results.blade.php --}}

@if($posts->count() > 0)
    @foreach($posts as $post)
        <div class="col-md-12 mb-4">
            <div class="card h-100 shadow-sm hover-shadow">
                <div class="card-body">
                    <h5 class="card-title">
                        <a href="{{ route('user.posts.show', $post) }}" class="text-decoration-none text-dark">
                            {{ $post->title }}
                        </a>
                    </h5>
                    
                    <div class="mb-2">
                        @foreach($post->categories as $category)
                            <span class="badge bg-secondary me-1">
                                {{ $category->name }}
                            </span>
                        @endforeach
                    </div>
                    
                    <p class="card-text">
                        {{ Str::limit($post->excerpt ?? strip_tags($post->content), 200) }}
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-calendar3"></i> 
                            {{ $post->created_at->format('F j, Y') }}
                        </small>
                        
                        <a href="{{ route('user.posts.show', $post) }}" class="btn btn-sm btn-outline-primary">
                            Read More <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="col-12">
        <div class="text-center py-5">
            <i class="bi bi-search display-1 text-muted"></i>
            <h4 class="mt-3">No posts found</h4>
            <p class="text-muted">Try adjusting your search criteria.</p>
        </div>
    </div>
@endif