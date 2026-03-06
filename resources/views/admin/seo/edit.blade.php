@extends('layouts.admin')

@section('content')
<div class="container">
    <h3>SEO Settings</h3>

    <form action="{{ route('admin.seo.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <h5>Basic SEO</h5>
        <input type="text" name="site_name" value="{{ $seo->site_name ?? '' }}" placeholder="Site Name" class="form-control mb-2">
        <input type="text" name="default_title" value="{{ $seo->default_title ?? '' }}" placeholder="Default Title" class="form-control mb-2">
        <textarea name="meta_description" class="form-control mb-2" placeholder="Meta Description">{{ $seo->meta_description ?? '' }}</textarea>
        <textarea name="meta_keywords" class="form-control mb-2" placeholder="Meta Keywords">{{ $seo->meta_keywords ?? '' }}</textarea>
        <input type="text" name="author" value="{{ $seo->author ?? '' }}" placeholder="Author" class="form-control mb-2">
        <input type="text" name="canonical_url" value="{{ $seo->canonical_url ?? '' }}" placeholder="Canonical URL" class="form-control mb-4">

        <h5>Open Graph</h5>
        <input type="text" name="og_title" value="{{ $seo->og_title ?? '' }}" placeholder="OG Title" class="form-control mb-2">
        <textarea name="og_description" class="form-control mb-2" placeholder="OG Description">{{ $seo->og_description ?? '' }}</textarea>
        <input type="file" name="og_image" class="form-control mb-2">
        <select name="og_type" class="form-control mb-4">
            <option value="website">Website</option>
            <option value="article">Article</option>
        </select>

        <h5>Twitter Card</h5>
        <select name="twitter_card" class="form-control mb-2">
            <option value="summary">Summary</option>
            <option value="summary_large_image">Summary Large Image</option>
        </select>
        <input type="text" name="twitter_title" value="{{ $seo->twitter_title ?? '' }}" placeholder="Twitter Title" class="form-control mb-2">
        <textarea name="twitter_description" class="form-control mb-2">{{ $seo->twitter_description ?? '' }}</textarea>
        <input type="file" name="twitter_image" class="form-control mb-2">
        <input type="text" name="twitter_site" value="{{ $seo->twitter_site ?? '' }}" placeholder="@handle" class="form-control mb-4">

        <h5>Other SEO</h5>
        <input type="file" name="favicon" class="form-control mb-2">
        <input type="file" name="site_logo" class="form-control mb-2">
        <input type="text" name="google_analytics_id" value="{{ $seo->google_analytics_id ?? '' }}" placeholder="Google Analytics ID" class="form-control mb-2">
        <input type="text" name="google_verification" value="{{ $seo->google_verification ?? '' }}" placeholder="Google Verification Code" class="form-control mb-2">
        <input type="text" name="robots_meta" value="{{ $seo->robots_meta ?? '' }}" placeholder="index, follow" class="form-control mb-4">

        <button type="submit" class="btn btn-primary">Save SEO Settings</button>
    </form>
</div>
@endsection