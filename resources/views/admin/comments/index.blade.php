@extends('layouts.admin')

@section('content')
<div class="content-inner container-fluid pb-0">
    <div class="row">
        <div class="col-sm-12">

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">All User Comments</h4>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 comments-table">
                            <thead>
                                <tr class="bg-white">
                                    <th class="bg-primary text-white">User</th>
                                    <th class="bg-primary text-white">Comment</th>
                                    <th class="bg-primary text-white">Post</th>
                                    <th class="bg-primary text-white">Date</th>
                                    <th class="bg-primary text-white">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($comments as $comment)
                                <tr>
                                    <td>{{ $comment->user->name ?? 'Guest' }}</td>

                                    <td>{{ $comment->content }}</td>

                                    <td>{{ $comment->post->title ?? 'N/A' }}</td>

                                    <td>{{ $comment->created_at->format('j F, Y') }}</td>

                                    <td>
                                        <form action="{{ route('admin.comments.delete', $comment->id) }}" method="POST" onsubmit="return confirm('Delete this comment?')">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger-subtle btn-sm">
                                                <i class="ph ph-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-danger">
                                        No comments found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $comments->links() }}
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .comments-table th,
    .comments-table td {
        font-size: 12px;
        padding: 4px 6px;
        white-space: nowrap;
    }

    .comments-table {
        font-size: 12px;
    }

    .card-title {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .comments-table th,
    .comments-table td {
        font-size: 10px;
        padding: 2px 4px;
    }

    .card-title {
        font-size: 14px;
    }
}
</style>

@endsection