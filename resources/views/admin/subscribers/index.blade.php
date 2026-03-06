@extends('layouts.admin')

@section('content')
<div class="content-inner container-fluid pb-0">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">All Subscribers</div>
                <div class="card-body">
                    @forelse($subscribers as $subscriber)
                        <div class="mb-2 border-bottom pb-1">
                            <p>{{ $subscriber->email }}</p>
                        </div>
                    @empty
                        <p>No subscribers yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection