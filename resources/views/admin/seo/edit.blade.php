@extends('layouts.admin')

@section('title', 'SEO settings')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">SEO settings</h1>
        <p class="ad-head__sub">Site-wide defaults for search engines and social sharing. Posts can override these.</p>
    </div>
</div>

<form action="{{ route('admin.seo.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="ad-grid ad-grid--main">
        <div class="ad-stack">
            <div class="ad-card">
                <div class="ad-card__head"><h3 class="ad-card__title"><i class="bi bi-globe2"></i> Basic SEO</h3></div>
                <div class="ad-card__body">
                    <div class="ad-grid ad-grid--2">
                        <div class="ad-field">
                            <label class="ad-label" for="site_name">Site name</label>
                            <input type="text" name="site_name" id="site_name" class="ad-input" value="{{ old('site_name', $seo->site_name ?? '') }}" placeholder="BriefMedia">
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="author">Author</label>
                            <input type="text" name="author" id="author" class="ad-input" value="{{ old('author', $seo->author ?? '') }}" placeholder="BriefMedia Team">
                        </div>
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="default_title">Default title <small>Browser tab &amp; search results</small></label>
                        <input type="text" name="default_title" id="default_title" class="ad-input" value="{{ old('default_title', $seo->default_title ?? '') }}"
                               data-counter="default_title_count" data-recommended="60">
                        <div class="ad-help"><span></span><span class="ad-counter"><span id="default_title_count">0</span>/60</span></div>
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="meta_description">Meta description</label>
                        <textarea name="meta_description" id="meta_description" rows="3" class="ad-textarea"
                                  data-counter="seo_description_count" data-recommended="160">{{ old('meta_description', $seo->meta_description ?? '') }}</textarea>
                        <div class="ad-help"><span></span><span class="ad-counter"><span id="seo_description_count">0</span>/160</span></div>
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="meta_keywords">Meta keywords <small>Comma separated</small></label>
                        <textarea name="meta_keywords" id="meta_keywords" rows="2" class="ad-textarea">{{ old('meta_keywords', $seo->meta_keywords ?? '') }}</textarea>
                    </div>
                    <div class="ad-grid ad-grid--2">
                        <div class="ad-field">
                            <label class="ad-label" for="canonical_url">Canonical URL</label>
                            <input type="text" name="canonical_url" id="canonical_url" class="ad-input" value="{{ old('canonical_url', $seo->canonical_url ?? '') }}" placeholder="https://www.briefmediablog.com">
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="robots_meta">Robots meta</label>
                            <input type="text" name="robots_meta" id="robots_meta" class="ad-input" value="{{ old('robots_meta', $seo->robots_meta ?? '') }}" placeholder="index, follow">
                        </div>
                    </div>
                </div>
            </div>

            <div class="ad-card">
                <div class="ad-card__head"><h3 class="ad-card__title"><i class="bi bi-share"></i> Social sharing</h3></div>
                <div class="ad-card__body">
                    <div class="ad-grid ad-grid--2">
                        <div>
                            <h4 class="ad-label" style="margin-bottom: 12px;"><span><i class="bi bi-facebook" style="color: #1877f2;"></i> Open Graph</span></h4>
                            <div class="ad-field">
                                <label class="ad-label" for="og_title">OG title</label>
                                <input type="text" name="og_title" id="og_title" class="ad-input" value="{{ old('og_title', $seo->og_title ?? '') }}">
                            </div>
                            <div class="ad-field">
                                <label class="ad-label" for="og_description">OG description</label>
                                <textarea name="og_description" id="og_description" rows="3" class="ad-textarea">{{ old('og_description', $seo->og_description ?? '') }}</textarea>
                            </div>
                            <div class="ad-field">
                                <label class="ad-label" for="og_type">OG type</label>
                                <select name="og_type" id="og_type" class="ad-select">
                                    @foreach(['website' => 'Website', 'article' => 'Article'] as $ogTypeValue => $ogTypeLabel)
                                        <option value="{{ $ogTypeValue }}" {{ old('og_type', $seo->og_type ?? 'website') === $ogTypeValue ? 'selected' : '' }}>{{ $ogTypeLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ad-field">
                                <label class="ad-label">OG image <small>1200 × 630 recommended</small></label>
                                @if(!empty($seo?->og_image))
                                    <div class="ad-current-img"><img src="{{ asset('storage/' . $seo->og_image) }}" alt=""><span class="ad-post-cell__sub">Current image</span></div>
                                @endif
                                <input type="file" name="og_image" accept="image/*" class="ad-file">
                            </div>
                        </div>

                        <div>
                            <h4 class="ad-label" style="margin-bottom: 12px;"><span><i class="bi bi-twitter-x"></i> Twitter / X</span></h4>
                            <div class="ad-field">
                                <label class="ad-label" for="twitter_card">Card type</label>
                                <select name="twitter_card" id="twitter_card" class="ad-select">
                                    @foreach(['summary' => 'Summary', 'summary_large_image' => 'Summary with large image'] as $cardValue => $cardLabel)
                                        <option value="{{ $cardValue }}" {{ old('twitter_card', $seo->twitter_card ?? 'summary') === $cardValue ? 'selected' : '' }}>{{ $cardLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ad-field">
                                <label class="ad-label" for="twitter_title">Twitter title</label>
                                <input type="text" name="twitter_title" id="twitter_title" class="ad-input" value="{{ old('twitter_title', $seo->twitter_title ?? '') }}">
                            </div>
                            <div class="ad-field">
                                <label class="ad-label" for="twitter_description">Twitter description</label>
                                <textarea name="twitter_description" id="twitter_description" rows="3" class="ad-textarea">{{ old('twitter_description', $seo->twitter_description ?? '') }}</textarea>
                            </div>
                            <div class="ad-field">
                                <label class="ad-label" for="twitter_site">Twitter handle</label>
                                <input type="text" name="twitter_site" id="twitter_site" class="ad-input" value="{{ old('twitter_site', $seo->twitter_site ?? '') }}" placeholder="@handle">
                            </div>
                            <div class="ad-field">
                                <label class="ad-label">Twitter image</label>
                                @if(!empty($seo?->twitter_image))
                                    <div class="ad-current-img"><img src="{{ asset('storage/' . $seo->twitter_image) }}" alt=""><span class="ad-post-cell__sub">Current image</span></div>
                                @endif
                                <input type="file" name="twitter_image" accept="image/*" class="ad-file">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ad-stack" style="position: sticky; top: 84px;">
            <div class="ad-card">
                <div class="ad-card__head"><h3 class="ad-card__title"><i class="bi bi-gear"></i> Brand &amp; tracking</h3></div>
                <div class="ad-card__body">
                    <div class="ad-field">
                        <label class="ad-label">Favicon <small>Square PNG, 512 × 512</small></label>
                        @if(!empty($seo?->favicon))
                            <div class="ad-current-img"><img src="{{ asset('storage/' . $seo->favicon) }}" alt="" style="width: 32px; height: 32px;"><span class="ad-post-cell__sub">Current favicon</span></div>
                        @endif
                        <input type="file" name="favicon" accept="image/*" class="ad-file">
                    </div>
                    <div class="ad-field">
                        <label class="ad-label">Site logo</label>
                        @if(!empty($seo?->site_logo))
                            <div class="ad-current-img"><img src="{{ asset('storage/' . $seo->site_logo) }}" alt="" style="object-fit: contain;"><span class="ad-post-cell__sub">Current logo</span></div>
                        @endif
                        <input type="file" name="site_logo" accept="image/*" class="ad-file">
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="google_analytics_id">Google Analytics ID</label>
                        <input type="text" name="google_analytics_id" id="google_analytics_id" class="ad-input" value="{{ old('google_analytics_id', $seo->google_analytics_id ?? '') }}" placeholder="G-XXXXXXXXXX">
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="google_verification">Google verification code</label>
                        <input type="text" name="google_verification" id="google_verification" class="ad-input" value="{{ old('google_verification', $seo->google_verification ?? '') }}">
                    </div>

                    <button type="submit" class="ad-btn ad-btn--primary" style="width: 100%;"><i class="bi bi-check2"></i> Save settings</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
