@extends('layouts.admin')

@section('content')
<div class="content-inner container-fluid pb-0" id="page_layout">
    <div class="row">
        <div class="col-sm-12">

            <!-- Links -->
            <div class="mb-3">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    ← Back to Categories List
                </a>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Deleted Categories</h4>
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
                        <table class="table table-bordered mb-0 trashed-category-table">
                            <thead>
                                <tr class="bg-white">
                                    <th class="bg-primary text-white">#</th>
                                    <th class="bg-primary text-white">Name</th>
                                    <th class="bg-primary text-white">Description</th>
                                    <th class="bg-primary text-white">Deleted At</th>
                                    <th class="bg-primary text-white">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($categories as $category)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    {{-- Name --}}
                                    <td>{{ $category->name }}</td>

                                    {{-- Description --}}
                                    <td>{{ Str::limit($category->description, 50) ?? '—' }}</td>

                                    {{-- Deleted Date --}}
                                    <td>
                                        @if($category->deleted_at)
                                            {{ $category->deleted_at->format('j F, Y h:i A') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td>
                                        <div class="d-flex gap-2 align-items-center">
                                            
                                          <form action="{{ route('admin.categories.restore', $category->id) }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-sm btn-success">
        Restore
    </button>
</form>

                                            {{-- Force Delete --}}
                                            <form action="{{ route('admin.categories.forceDelete', $category->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Are you sure you want to permanently delete this category?');">
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
                                    <td colspan="6" class="text-center text-danger">
                                        No deleted categories found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        @if(method_exists($categories, 'links'))
                            <div class="mt-3 d-flex justify-content-center">
                                {{ $categories->links() }}
                            </div>
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Custom CSS for responsiveness --}}
<style>
@media (max-width: 768px) {
    .trashed-category-table th,
    .trashed-category-table td {
        font-size: 12px;
        padding: 4px 6px;
        white-space: nowrap;
    }

    .trashed-category-table {
        font-size: 12px;
    }

    .card-title {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .trashed-category-table th,
    .trashed-category-table td {
        font-size: 10px;
        padding: 2px 4px;
    }

    .card-title {
        font-size: 14px;
    }
}
</style>
@endsection