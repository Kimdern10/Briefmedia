@extends('layouts.admin')

@section('content')
<div class="content-inner container-fluid pb-0" id="page_layout">
    <div class="row">
        <div class="col-sm-12">

            <!-- Links -->
            <div class="mb-3">
                <a href="{{ route('admin.posts.trash') }}" class="btn btn-primary">View Trashed</a>
                <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">+ Add Post</a>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Posts List</h4>
                    </div>
                </div>

                <div class="card-body">

                    {{-- Success Message --}}
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 post-table">
                            <thead>
                                <tr class="bg-white">
                                    <th class="bg-primary text-white">#</th>
                                    <th class="bg-primary text-white">Title</th>
                                    <th class="bg-primary text-white">Category</th>
                                    <th class="bg-primary text-white">Status</th>
                                    <th class="bg-primary text-white">Image</th>
                                    <th class="bg-primary text-white">Created</th>
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
                                        @if($post->trashed())
                                            <span class="badge bg-danger">Trashed</span>
                                        @else
                                            @if($post->status === 'published')
                                                <span class="badge bg-success">Published</span>
                                            @else
                                                <span class="badge bg-secondary">Draft</span>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Image --}}
                                    <td>
                                        @if(!empty($post->images) && count($post->images) > 0)
                                            <img src="{{ asset('storage/' . $post->images[0]) }}"
                                                 width="80"
                                                 class="rounded">
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- Created Date --}}
                                    <td>{{ $post->created_at->format('d M Y') }}</td>

                                    {{-- Actions --}}
                                    <td>
                                        <div class="d-flex gap-2 align-items-center flex-wrap">

                                            @if(!$post->trashed())

                                                {{-- Publish / Unpublish --}}
                                                <form action="{{ route('admin.posts.toggle-status', $post->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    @if($post->status === 'published')
                                                        <button class="btn btn-sm btn-secondary">
                                                            Unpublish
                                                        </button>
                                                    @else
                                                        <button class="btn btn-sm btn-success">
                                                            Publish
                                                        </button>
                                                    @endif
                                                </form>

                                                {{-- Edit --}}
                                                <a href="{{ route('admin.posts.edit', $post->id) }}"
                                                   class="btn btn-warning btn-sm">
                                                    Edit
                                                </a>

                                                {{-- Trash --}}
                                                <form action="{{ route('admin.posts.destroy', $post->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Move this post to trash?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-danger-subtle btn-sm">
                                                        <i class="ph ph-trash"></i>
                                                    </button>
                                                </form>

                                            @else

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
                                                      method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-sm btn-dark"
                                                            onclick="return confirm('Delete permanently?')">
                                                        Delete Permanently
                                                    </button>
                                                </form>

                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-danger">
                                        No posts found
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

{{-- Custom Responsive CSS --}}
<style>
@media (max-width: 768px) {
    .post-table th,
    .post-table td {
        font-size: 12px;
        padding: 4px 6px;
        white-space: nowrap;
    }

    .post-table {
        font-size: 12px;
    }

    .card-title {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .post-table th,
    .post-table td {
        font-size: 10px;
        padding: 2px 4px;
    }

    .card-title {
        font-size: 14px;
    }
}
</style>
@endsection