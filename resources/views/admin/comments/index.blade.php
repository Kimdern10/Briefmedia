@extends('layouts.admin')

@section('content')
<div class="content-inner container-fluid pb-0">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">All User Comments</div>
                <div class="card-body">
                    @forelse($comments as $comment)
                        <div class="mb-3 border-bottom pb-2">
                            <strong>{{ $comment->user->name ?? 'Guest' }}:</strong>
                            <p>{{ $comment->content }}</p>
                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                        </div>
                    @empty
                        <p>No comments yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection