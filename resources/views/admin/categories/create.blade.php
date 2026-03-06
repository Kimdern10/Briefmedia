@extends('layouts.admin')

@section('content')
<div class="content-inner container-fluid pb-0" id="page_layout">
    <div class="row">
        <div class="col-sm-12">

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Create Category</h4>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.categories.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Category Description</label>
                            <textarea name="description" id="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary">Create Category</button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@push('styles')
<style>
    @media (max-width: 767px) {
        .card { padding: 10px !important; }
        .card-header .card-title { font-size: 18px !important; }
        .form-label { font-size: 14px !important; }
        .form-control { padding: 8px 10px !important; font-size: 14px !important; }
        textarea.form-control { font-size: 14px !important; }
        .btn { font-size: 14px !important; padding: 8px 12px !important; }
        .d-flex.gap-2 { flex-direction: column !important; gap: 10px !important; }
    }
</style>
@endpush

@endsection