@extends('layouts.admin')

@section('content')
<div class="content-inner container-fluid pb-0" id="page_layout">
    <div class="row">
        <div class="col-sm-12">

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Edit Post</h4>
                    </div>
                </div>

                <div class="card-body">

                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.posts.update', $post->id) }}" 
                          method="POST" 
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Category --}}
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Title --}}
                        <div class="mb-3">
                            <label class="form-label">Post Title</label>
                            <input type="text" 
                                   name="title" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   value="{{ old('title', $post->title) }}"
                                   required>
                            @error('title')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Excerpt --}}
                        <div class="mb-3">
                            <label class="form-label">Short Description (Excerpt)</label>
                            <textarea name="excerpt" 
                                      class="form-control @error('excerpt') is-invalid @enderror" 
                                      rows="3">{{ old('excerpt', $post->excerpt) }}</textarea>
                            @error('excerpt')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Content --}}
                        <div class="mb-3">
                            <label class="form-label">Full Content</label>
                            <textarea name="content" 
                                      id="editor" 
                                      class="form-control @error('content') is-invalid @enderror" 
                                      rows="6" 
                                      required>{{ old('content', $post->content) }}</textarea>
                            @error('content')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Featured Images --}}
                        <div class="row">
                            @for($i = 1; $i <= 4; $i++)
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Featured Image {{ $i }} 
                                            @if($i == 1)<span class="text-danger">*</span>@endif
                                        </label>

                                        @if($post->{'featured_image_'.$i})
                                            <div class="mb-2">
                                                <img src="{{ asset('storage/' . $post->{'featured_image_'.$i}) }}" 
                                                     width="100" 
                                                     class="rounded border">
                                                <div class="form-check mt-1">
                                                    <input type="checkbox" 
                                                           name="remove_featured_image_{{ $i }}" 
                                                           id="remove_featured_image_{{ $i }}"
                                                           class="form-check-input"
                                                           value="1">
                                                    <label class="form-check-label text-danger" 
                                                           for="remove_featured_image_{{ $i }}">
                                                        Remove this image
                                                    </label>
                                                </div>
                                            </div>
                                        @endif

                                        <input type="file" 
                                               name="featured_image_{{ $i }}" 
                                               class="form-control @error('featured_image_'.$i) is-invalid @enderror">
                                        @error('featured_image_'.$i)
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            @endfor
                        </div>

                        {{-- Status --}}
                        <div class="mb-3">
                            <label class="form-label">Post Status</label>
                            <select name="status" class="form-control" required>
                                <option value="draft"
                                    {{ old('status', $post->status) == 'draft' ? 'selected' : '' }}>
                                    Draft
                                </option>
                                <option value="published"
                                    {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>
                                    Published
                                </option>
                            </select>
                        </div>

                        {{-- ========================================= --}}
                        {{-- ENHANCED SEO SETTINGS --}}
                        {{-- ========================================= --}}
                        <hr>
                        <div class="card mt-4">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">🔍 SEO Settings</h5>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           name="enable_auto_seo" 
                                           id="enableAutoSeo"
                                           value="1"
                                           {{ old('enable_auto_seo', $post->enable_auto_seo ?? true) ? 'checked' : '' }}>
                                    <label class="custom-control-label text-white" for="enableAutoSeo">
                                        Auto-Generate SEO
                                    </label>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <!-- Auto SEO Notice -->
                                <div class="alert alert-info" id="autoSeoNotice" style="{{ old('enable_auto_seo', $post->enable_auto_seo ?? true) ? '' : 'display: none;' }}">
                                    <i class="fas fa-robot"></i> 
                                    <strong>Auto SEO is enabled!</strong> Fields left empty will be automatically generated from your content. 
                                    You can still manually override any field below.
                                </div>
                                
                                <div class="alert alert-warning" id="manualSeoNotice" style="{{ old('enable_auto_seo', $post->enable_auto_seo ?? true) ? 'display: none;' : '' }}">
                                    <i class="fas fa-edit"></i> 
                                    <strong>Manual SEO mode</strong> - All fields below will need to be filled manually.
                                </div>

                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs" id="seoTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="basic-seo-tab" data-toggle="tab" href="#basic-seo" role="tab">Basic SEO</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="social-seo-tab" data-toggle="tab" href="#social-seo" role="tab">Social Media</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="advanced-seo-tab" data-toggle="tab" href="#advanced-seo" role="tab">Advanced</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="preview-seo-tab" data-toggle="tab" href="#preview-seo" role="tab">Preview</a>
                                    </li>
                                </ul>

                                <!-- Tab panes -->
                                <div class="tab-content mt-3">
                                    <!-- Basic SEO Tab -->
                                    <div class="tab-pane active" id="basic-seo" role="tabpanel">
                                        <div class="form-group">
                                            <label>
                                                Meta Title 
                                                <small class="text-muted">({{ old('enable_auto_seo', $post->enable_auto_seo ?? true) ? 'Auto-generated from post title if empty' : 'Required' }})</small>
                                            </label>
                                            <div class="input-group">
                                                <input type="text" 
                                                       name="meta_title" 
                                                       class="form-control" 
                                                       value="{{ old('meta_title', $post->meta_title) }}"
                                                       maxlength="70"
                                                       id="meta_title"
                                                       placeholder="{{ old('enable_auto_seo', $post->enable_auto_seo ?? true) ? 'Leave empty to auto-generate' : '' }}">
                                                @if(old('enable_auto_seo', $post->enable_auto_seo ?? true))
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-light">
                                                            <i class="fas fa-magic text-info" title="Auto-generated"></i>
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            <small class="text-muted">Recommended: 50-60 characters. Current: <span id="meta_title_count">{{ strlen(old('meta_title', $post->meta_title ?? '')) }}</span></small>
                                            @error('meta_title')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>
                                                Meta Description 
                                                <small class="text-muted">({{ old('enable_auto_seo', $post->enable_auto_seo ?? true) ? 'Auto-generated from content if empty' : 'Required' }})</small>
                                            </label>
                                            <div class="input-group">
                                                <textarea name="meta_description" 
                                                          class="form-control" 
                                                          rows="3"
                                                          maxlength="320"
                                                          id="meta_description"
                                                          placeholder="{{ old('enable_auto_seo', $post->enable_auto_seo ?? true) ? 'Leave empty to auto-generate' : '' }}">{{ old('meta_description', $post->meta_description) }}</textarea>
                                                @if(old('enable_auto_seo', $post->enable_auto_seo ?? true))
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-light">
                                                            <i class="fas fa-magic text-info"></i>
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            <small class="text-muted">Recommended: 150-160 characters. Current: <span id="meta_description_count">{{ strlen(old('meta_description', $post->meta_description ?? '')) }}</span></small>
                                            @error('meta_description')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>Meta Keywords <small class="text-muted">(Comma separated)</small></label>
                                            <input type="text" 
                                                   name="meta_keywords" 
                                                   class="form-control" 
                                                   value="{{ old('meta_keywords', $post->meta_keywords) }}"
                                                   placeholder="blog, news, berifmedia, etc.">
                                            @error('meta_keywords')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>Meta Image <small class="text-muted">(For search engines)</small></label>
                                            
                                            @if($post->meta_image)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $post->meta_image) }}" 
                                                         width="100" 
                                                         class="rounded border">
                                                    <div class="form-check mt-1">
                                                        <input type="checkbox" 
                                                               name="remove_meta_image" 
                                                               id="remove_meta_image"
                                                               class="form-check-input"
                                                               value="1">
                                                        <label class="form-check-label text-danger" 
                                                               for="remove_meta_image">
                                                            Remove this image
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <input type="file" 
                                                   name="meta_image" 
                                                   class="form-control-file @error('meta_image') is-invalid @enderror">
                                            @error('meta_image')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Social Media Tab -->
                                    <div class="tab-pane" id="social-seo" role="tabpanel">
                                        <h6 class="mb-3">Open Graph (Facebook, LinkedIn)</h6>
                                        <div class="form-group">
                                            <label>OG Title</label>
                                            <input type="text" 
                                                   name="og_title" 
                                                   class="form-control" 
                                                   value="{{ old('og_title', $post->og_title) }}">
                                            @error('og_title')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>OG Description</label>
                                            <textarea name="og_description" 
                                                      class="form-control" 
                                                      rows="3">{{ old('og_description', $post->og_description) }}</textarea>
                                            @error('og_description')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>OG Image</label>
                                            
                                            @if($post->og_image)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $post->og_image) }}" 
                                                         width="100" 
                                                         class="rounded border">
                                                    <div class="form-check mt-1">
                                                        <input type="checkbox" 
                                                               name="remove_og_image" 
                                                               id="remove_og_image"
                                                               class="form-check-input"
                                                               value="1">
                                                        <label class="form-check-label text-danger" 
                                                               for="remove_og_image">
                                                            Remove this image
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <input type="file" 
                                                   name="og_image" 
                                                   class="form-control-file @error('og_image') is-invalid @enderror">
                                            @error('og_image')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <hr class="my-4">

                                        <h6 class="mb-3">Twitter Card</h6>
                                        <div class="form-group">
                                            <label>Twitter Title</label>
                                            <input type="text" 
                                                   name="twitter_title" 
                                                   class="form-control" 
                                                   value="{{ old('twitter_title', $post->twitter_title) }}">
                                            @error('twitter_title')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>Twitter Description</label>
                                            <textarea name="twitter_description" 
                                                      class="form-control" 
                                                      rows="3">{{ old('twitter_description', $post->twitter_description) }}</textarea>
                                            @error('twitter_description')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>Twitter Image</label>
                                            
                                            @if($post->twitter_image)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $post->twitter_image) }}" 
                                                         width="100" 
                                                         class="rounded border">
                                                    <div class="form-check mt-1">
                                                        <input type="checkbox" 
                                                               name="remove_twitter_image" 
                                                               id="remove_twitter_image"
                                                               class="form-check-input"
                                                               value="1">
                                                        <label class="form-check-label text-danger" 
                                                               for="remove_twitter_image">
                                                            Remove this image
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <input type="file" 
                                                   name="twitter_image" 
                                                   class="form-control-file @error('twitter_image') is-invalid @enderror">
                                            @error('twitter_image')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Advanced Tab -->
                                    <div class="tab-pane" id="advanced-seo" role="tabpanel">
                                        <div class="form-group">
                                            <label>Canonical URL <small class="text-muted">(Optional)</small></label>
                                            <input type="url" 
                                                   name="canonical_url" 
                                                   class="form-control" 
                                                   value="{{ old('canonical_url', $post->canonical_url) }}"
                                                   placeholder="https://example.com/original-post">
                                            @error('canonical_url')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>Schema Markup <small class="text-muted">(JSON-LD)</small></label>
                                            <textarea name="schema_markup" 
                                                      class="form-control" 
                                                      rows="5">{{ old('schema_markup', $post->schema_markup) }}</textarea>
                                            @error('schema_markup')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   name="no_index" 
                                                   class="form-check-input" 
                                                   id="no_index"
                                                   value="1"
                                                   {{ old('no_index', $post->no_index) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="no_index">No Index (Hide from search engines)</label>
                                        </div>

                                        <div class="form-check mt-2">
                                            <input type="checkbox" 
                                                   name="no_follow" 
                                                   class="form-check-input" 
                                                   id="no_follow"
                                                   value="1"
                                                   {{ old('no_follow', $post->no_follow) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="no_follow">No Follow (Don't follow links on this page)</label>
                                        </div>
                                    </div>

                                    <!-- Preview Tab -->
                                    <div class="tab-pane" id="preview-seo" role="tabpanel">
                                        <h6 class="mb-3">Google Search Preview</h6>
                                        <div class="google-preview p-3 border rounded bg-light">
                                            <div class="text-primary" style="font-size: 20px;" id="preview-title">
                                                {{ old('meta_title', $post->meta_title ?? $post->title) }}
                                            </div>
                                            <div class="text-success" style="font-size: 14px;" id="preview-url">
                                                {{ url('/') }}/posts/{{ $post->slug }}
                                            </div>
                                            <div class="text-muted" style="font-size: 14px;" id="preview-description">
                                                {{ old('meta_description', $post->meta_description ?? 'Post description will appear here...') }}
                                            </div>
                                        </div>

                                        <h6 class="mt-4 mb-3">Facebook/Open Graph Preview</h6>
                                        <div class="facebook-preview border rounded" style="max-width: 500px;">
                                            <div class="p-3 bg-light border-bottom">
                                                <strong>BerifMedia</strong> · 1 hr
                                            </div>
                                            <div id="preview-og-image" class="bg-secondary" 
                                                 style="height: 200px; background-size: cover; background-position: center;
                                                        {{ $post->og_image ? 'background-image: url(' . asset('storage/' . $post->og_image) . ')' : '' }}">
                                            </div>
                                            <div class="p-3">
                                                <div class="text-muted small" id="preview-og-url">berifmedia.com</div>
                                                <div class="font-weight-bold" id="preview-og-title">{{ old('og_title', $post->og_title ?? $post->meta_title ?? $post->title) }}</div>
                                                <div class="text-muted small" id="preview-og-description">{{ old('og_description', $post->og_description ?? $post->meta_description ?? 'Post description...') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap mt-4">
                            <button type="submit" class="btn btn-primary">
                                Update Post
                            </button>
                            <a href="{{ route('admin.posts.index') }}" 
                               class="btn btn-secondary">
                                Back
                            </a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- CKEditor --}}
@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('editor');
    
    // Character counters
    document.getElementById('meta_title')?.addEventListener('keyup', function() {
        document.getElementById('meta_title_count').textContent = this.value.length;
    });

    document.getElementById('meta_description')?.addEventListener('keyup', function() {
        document.getElementById('meta_description_count').textContent = this.value.length;
    });

    // Toggle auto SEO
    document.getElementById('enableAutoSeo')?.addEventListener('change', function() {
        const autoMode = this.checked;
        document.getElementById('autoSeoNotice').style.display = autoMode ? 'block' : 'none';
        document.getElementById('manualSeoNotice').style.display = autoMode ? 'none' : 'block';
        
        // Update placeholders
        const inputs = document.querySelectorAll('[name="meta_title"], [name="meta_description"], [name="meta_keywords"]');
        inputs.forEach(input => {
            input.placeholder = autoMode ? 'Leave empty to auto-generate' : '';
        });
    });

    // Live preview updates
    function updatePreview() {
        const title = document.querySelector('[name="meta_title"]')?.value || document.querySelector('[name="title"]')?.value || '{{ $post->title }}';
        const description = document.querySelector('[name="meta_description"]')?.value || '{{ $post->meta_description ?? 'Post description...' }}';
        
        const previewTitle = document.getElementById('preview-title');
        const previewDesc = document.getElementById('preview-description');
        
        if (previewTitle) previewTitle.textContent = title;
        if (previewDesc) previewDesc.textContent = description;
        
        // OG preview
        const ogTitle = document.querySelector('[name="og_title"]')?.value || title;
        const ogDesc = document.querySelector('[name="og_description"]')?.value || description;
        
        const previewOgTitle = document.getElementById('preview-og-title');
        const previewOgDesc = document.getElementById('preview-og-description');
        
        if (previewOgTitle) previewOgTitle.textContent = ogTitle;
        if (previewOgDesc) previewOgDesc.textContent = ogDesc;
    }

    // Listen for changes
    document.querySelectorAll('[name="meta_title"], [name="meta_description"], [name="title"], [name="og_title"], [name="og_description"]')
        .forEach(input => {
            if (input) {
                input.addEventListener('keyup', updatePreview);
            }
        });
</script>
@endpush

{{-- Responsive Styling --}}
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
    .nav-tabs .nav-link { padding: 8px 10px; font-size: 13px; }
    .input-group-text { padding: 8px 10px; }
}

.google-preview {
    background-color: #f8f9fa;
}

.facebook-preview {
    background-color: #fff;
    overflow: hidden;
}

.custom-control-label {
    cursor: pointer;
}

.nav-tabs .nav-link {
    color: #495057;
}

.nav-tabs .nav-link.active {
    color: #0b7bcc;
    font-weight: 600;
}

.input-group-text {
    background-color: #f8f9fa;
}

.form-check-input {
    margin-top: 0.25rem;
}
</style>
@endpush

@endsection