@extends('layouts.admin') 

@section('content')
<div class="content-inner container-fluid pb-0" id="page_layout">
    <div class="row">
        <div class="col-sm-12">

            <!-- Link to trashed users -->
            <div class="mb-3">
                <a href="{{ route('users.trashed') }}" class="btn btn-primary">View Deleted Users</a>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">User List</h4>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 users-table">
                            <thead>
                                <tr class="bg-white">
                                    <th class="bg-primary text-white">Name</th>
                                    <th class="bg-primary text-white">Email</th>
                                    <th class="bg-primary text-white">Join Date</th>
                                    <th class="bg-primary text-white">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->name ?? 'N/A' }}</td>
                                    <td>{{ $user->email ?? 'N/A' }}</td>
                                    <td>{{ $user->created_at ? $user->created_at->format('j F, Y') : 'N/A' }}</td>
                                    <td>
                                        <div class="d-flex gap-2 align-items-center">
                                            <!-- Ban/Unban -->
                                            <form action="{{ $user->active ? route('user.ban', $user->id) : route('user.unban', $user->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-primary-subtle btn-sm">
                                                    {{ $user->active ? 'Ban' : 'Unban' }}
                                                </button>
                                            </form>

                                            <!-- Soft Delete -->
                                            <form action="{{ route('user.delete', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger-subtle btn-sm">
                                                    <i class="ph ph-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-danger">No users found</td>
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
<style>
@media (max-width: 768px) {
    .users-table th,
    .users-table td {
        font-size: 12px;
        padding: 4px 6px;
        white-space: nowrap;
    }

    .users-table {
        font-size: 12px;
    }

    .card-title {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .users-table th,
    .users-table td {
        font-size: 10px;
        padding: 2px 4px;
    }

    .card-title {
        font-size: 14px;
    }
}
</style>
@endsection
