@extends('layouts.admin')

@section('content')
<div class="content-inner container-fluid pb-0" id="page_layout">
    <div class="row">
        <div class="col-sm-12">

            <!-- Links -->
            <div class="mb-3">
                <a href="{{ route('admin.categories.trash') }}" class="btn btn-primary">View Trashed</a>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">+ Add Category</a>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Categories List</h4>
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
                        <table class="table table-bordered mb-0 category-table">
                            <thead>
                                <tr class="bg-white">
                                    <th class="bg-primary text-white">#</th>
                                    <th class="bg-primary text-white">Name</th>
                                    <th class="bg-primary text-white">Description</th>
                                    <th class="bg-primary text-white">Created</th>
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

                                    {{-- Created Date --}}
                                    <td>{{ $category->created_at->format('d M Y') }}</td>

                                    {{-- Actions --}}
                                    <td>
                                        <div class="d-flex gap-2 align-items-center flex-wrap">

                                            {{-- Edit --}}
                                            <a href="{{ route('admin.categories.edit', $category->id) }}"
                                               class="btn btn-warning btn-sm">
                                                Edit
                                            </a>

                                            {{-- Trash --}}
                                            <form action="{{ route('admin.categories.destroy', $category->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Move this category to trash?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-danger-subtle btn-sm">
                                                    <i class="ph ph-trash"></i>
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>

                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-danger">
                                        No categories found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="mt-3 d-flex justify-content-center">
                            <p>{{ $categories->links() }}</p>
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
    .category-table th,
    .category-table td {
        font-size: 12px;
        padding: 4px 6px;
        white-space: nowrap;
    }

    .category-table {
        font-size: 12px;
    }

    .card-title {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .category-table th,
    .category-table td {
        font-size: 10px;
        padding: 2px 4px;
    }

    .card-title {
        font-size: 14px;
    }
}
</style>
@endsection