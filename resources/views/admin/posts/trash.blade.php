@extends('layouts.admin')

@section('content')
<div class="content-inner container-fluid pb-0" id="page_layout">
    <div class="row">
        <div class="col-sm-12">

            <!-- Links -->
            <div class="mb-3">
                <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">
                    ← Back to Posts List
                </a>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Deleted Posts</h4>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 trashed-post-table">
                            <thead>
                                <tr class="bg-white">
                                    <th class="bg-primary text-white">#</th>
                                    <th class="bg-primary text-white">Title</th>
                                    <th class="bg-primary text-white">Category</th>
                                    <th class="bg-primary text-white">Status</th>
                                    <th class="bg-primary text-white">Image</th>
                                    <th class="bg-primary text-white">Deleted At</th>
                                    <th class="bg-primary text-white">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($posts as $post)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    {{-- Title --}}
                                    <td>{{ $post->title }}</td>

                                    {{-- Category --}}
                                    <td>{{ $post->category->name ?? '—' }}</td>

                                    {{-- Status --}}
                                    <td>
                                        @if($post->status === 'published')
                                            <span class="badge bg-success">Published</span>
                                        @else
                                            <span class="badge bg-secondary">Draft</span>
                                        @endif
                                    </td>

                                    {{-- Image --}}
                                    <td>
                                        @if($post->featured_image_1)
                                            <img src="{{ asset('storage/' . $post->featured_image_1) }}"
                                                 width="80"
                                                 class="rounded img-fluid">
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- Deleted Date --}}
                                    <td>
                                        {{ $post->deleted_at->format('j F, Y h:i A') }}
                                    </td>

                                    {{-- Actions --}}
                                    <td>
                                        <div class="d-flex gap-2 align-items-center">
                                            
                                            {{-- Restore --}}
                                            <form action="{{ route('admin.posts.restore', $post->id) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-success">
                                                    Restore
                                                </button>
                                            </form>

                                            {{-- Force Delete --}}
                                            <form action="{{ route('admin.posts.forceDelete', $post->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Are you sure you want to permanently delete this post?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-danger btn-sm">
                                                    Delete Permanently
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>

                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-danger">
                                        No deleted posts found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $posts->links() }}
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Custom CSS for responsiveness --}}
<style>
@media (max-width: 768px) {
    .trashed-post-table th,
    .trashed-post-table td {
        font-size: 12px;
        padding: 4px 6px;
        white-space: nowrap;
    }

    .trashed-post-table {
        font-size: 12px;
    }

    .card-title {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .trashed-post-table th,
    .trashed-post-table td {
        font-size: 10px;
        padding: 2px 4px;
    }

    .card-title {
        font-size: 14px;
    }
}
</style>
@endsection