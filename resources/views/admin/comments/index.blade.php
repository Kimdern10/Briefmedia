@extends('layouts.admin')

@section('title', 'Comments')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Comments</h1>
        <p class="ad-head__sub">Keep the conversation healthy. Remove spam or abuse.</p>
    </div>
    <div class="ad-head__actions">
        
    </div>
</div>

<livewire:admin.comments-table />
@endsection
