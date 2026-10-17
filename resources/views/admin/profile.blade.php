@extends('layouts.admin')

@section('title', 'My profile')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">My profile</h1>
        <p class="ad-head__sub">Your photo, details and password. Changes save straight away.</p>
    </div>
</div>

<div class="ad-grid ad-grid--main" style="grid-template-columns: 320px minmax(0, 1fr);">
    <div class="ad-stack">
        <livewire:profile-photo variant="admin" />
    </div>
    <div class="ad-stack">
        <livewire:profile-form variant="admin" />
        <livewire:change-password variant="admin" />
    </div>
</div>
@endsection
