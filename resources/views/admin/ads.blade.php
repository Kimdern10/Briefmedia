@extends('layouts.admin')

@section('title', 'Ads')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Ads</h1>
        <p class="ad-head__sub">Paste AdSense or sponsor code for each place on the site, then switch it on. Changes show straight away.</p>
    </div>
</div>

<livewire:admin.ad-slots-manager />
@endsection
