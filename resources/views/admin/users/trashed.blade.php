@extends('layouts.admin')

@section('content')
<div class="content-inner container-fluid pb-0" id="page_layout">
    <div class="row">
        <div class="col-sm-12">

            <div class="mb-3">
                <a href="{{ route('user.list') }}" class="btn btn-secondary">← Back to User List</a>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Trashed Users</h4>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 trashed-users-table">
                            <thead>
                                <tr class="bg-white">
                                    <th class="bg-primary text-white">#</th>
                                    <th class="bg-primary text-white">Name</th>
                                    <th class="bg-primary text-white">Email</th>
                                    <th class="bg-primary text-white">Deleted At</th>
                                    <th class="bg-primary text-white">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $user->name ?? 'N/A' }}</td>
                                    <td>{{ $user->email ?? 'N/A' }}</td>
                                    <td>{{ $user->deleted_at ? $user->deleted_at->format('j F, Y H:i') : 'N/A' }}</td>
                                    <td>
                                        <div class="d-flex gap-2 align-items-center">
                                            <!-- Restore -->
                                            <form action="{{ route('user.restore', $user->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-success btn-sm">Restore</button>
                                            </form>

                                            <!-- Permanently Delete -->
                                            <form action="{{ route('user.forceDelete', $user->id) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to permanently delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-danger">No trashed users found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $users->links() }}
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
    .trashed-users-table th,
    .trashed-users-table td {
        font-size: 12px;
        padding: 4px 6px;
        white-space: nowrap;
    }

    .trashed-users-table {
        font-size: 12px;
    }

    .card-title {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .trashed-users-table th,
    .trashed-users-table td {
        font-size: 10px;
        padding: 2px 4px;
    }

    .card-title {
        font-size: 14px;
    }
}
</style>
@endsection
