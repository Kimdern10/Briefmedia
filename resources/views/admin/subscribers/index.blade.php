@extends('layouts.admin')

@section('content')
<div class="content-inner container-fluid pb-0">
    <div class="row">
        <div class="col-sm-12">

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">All Subscribers</h4>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">

                        <table class="table table-bordered mb-0 subscribers-table">
                            <thead>
                                <tr class="bg-white">
                                    <th class="bg-primary text-white">Email</th>
                                    <th class="bg-primary text-white">Subscribed Date</th>
                                    <th class="bg-primary text-white">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($subscribers as $subscriber)
                                <tr>
                                    <td>{{ $subscriber->email }}</td>

                                    <td>
                                        {{ $subscriber->created_at->format('j F, Y') }}
                                    </td>

                                    <td>
                                        <form action="{{ route('admin.subscribers.delete', $subscriber->id) }}" method="POST" onsubmit="return confirm('Delete this subscriber?')">
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
                                    <td colspan="3" class="text-center text-danger">
                                        No subscribers found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>

                        </table>

                        <!-- Pagination -->
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $subscribers->links() }}
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .subscribers-table th,
    .subscribers-table td {
        font-size: 12px;
        padding: 4px 6px;
        white-space: nowrap;
    }

    .card-title {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .subscribers-table th,
    .subscribers-table td {
        font-size: 10px;
        padding: 2px 4px;
    }

    .card-title {
        font-size: 14px;
    }
}
</style>

@endsection