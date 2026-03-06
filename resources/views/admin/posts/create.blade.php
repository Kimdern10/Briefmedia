@extends('layouts.admin')

@section('content')
<div class="content-inner container-fluid pb-0" id="page_layout">
    <div class="row">
        <div class="col-sm-12">

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Create Post</h4>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Category --}}
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                   placeholder="Enter post title" value="{{ old('title') }}" required>
                            @error('title')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Excerpt --}}
                        <div class="mb-3">
                            <label class="form-label">Short Description (Excerpt)</label>
                            <textarea name="excerpt" class="form-control @error('excerpt') is-invalid @enderror" 
                                      rows="3" placeholder="Short summary of the post">{{ old('excerpt') }}</textarea>
                            @error('excerpt')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Content --}}
                        <div class="mb-3">
                            <label class="form-label">Full Content</label>
                            <textarea name="content" id="editor" class="form-control @error('content') is-invalid @enderror" 
                                      rows="6" required>{{ old('content') }}</textarea>
                            @error('content')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Featured Images --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Featured Image 1 <span class="text-danger">*</span></label>
                                    <input type="file" name="featured_image_1" class="form-control @error('featured_image_1') is-invalid @enderror" required>
                                    @error('featured_image_1')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Featured Image 2 (Optional)</label>
                                    <input type="file" name="featured_image_2" class="form-control @error('featured_image_2') is-invalid @enderror">
                                    @error('featured_image_2')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Featured Image 3 (Optional)</label>
                                    <input type="file" name="featured_image_3" class="form-control @error('featured_image_3') is-invalid @enderror">
                                    @error('featured_image_3')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Featured Image 4 (Optional)</label>
                                    <input type="file" name="featured_image_4" class="form-control @error('featured_image_4') is-invalid @enderror">
                                    @error('featured_image_4')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="mb-3">
                            <label class="form-label">Post Status</label>
                            <select name="status" class="form-control" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
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
                                           {{ old('enable_auto_seo', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label text-white" for="enableAutoSeo">
                                        Auto-Generate SEO
                                    </label>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <!-- Auto SEO Notice -->
                                <div class="alert alert-info" id="autoSeoNotice" style="{{ old('enable_auto_seo', true) ? '' : 'display: none;' }}">
                                    <i class="fas fa-robot"></i> 
                                    <strong>Auto SEO is enabled!</strong> Fields left empty will be automatically generated from your content. 
                                    You can still manually override any field below.
                                </div>
                                
                                <div class="alert alert-warning" id="manualSeoNotice" style="{{ old('enable_auto_seo', true) ? 'display: none;' : '' }}">
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
                                                <small class="text-muted">({{ old('enable_auto_seo', true) ? 'Auto-generated from post title if empty' : 'Required' }})</small>
                                            </label>
                                            <div class="input-group">
                                                <input type="text" 
                                                       name="meta_title" 
                                                       class="form-control" 
                                                       value="{{ old('meta_title') }}"
                                                       maxlength="70"
                                                       id="meta_title"
                                                       placeholder="{{ old('enable_auto_seo', true) ? 'Leave empty to auto-generate' : '' }}">
                                                @if(old('enable_auto_seo', true))
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-light">
                                                            <i class="fas fa-magic text-info" title="Auto-generated"></i>
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            <small class="text-muted">Recommended: 50-60 characters. Current: <span id="meta_title_count">0</span></small>
                                            @error('meta_title')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>
                                                Meta Description 
                                                <small class="text-muted">({{ old('enable_auto_seo', true) ? 'Auto-generated from content if empty' : 'Required' }})</small>
                                            </label>
                                            <div class="input-group">
                                                <textarea name="meta_description" 
                                                          class="form-control" 
                                                          rows="3"
                                                          maxlength="320"
                                                          id="meta_description"
                                                          placeholder="{{ old('enable_auto_seo', true) ? 'Leave empty to auto-generate' : '' }}">{{ old('meta_description') }}</textarea>
                                                @if(old('enable_auto_seo', true))
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-light">
                                                            <i class="fas fa-magic text-info"></i>
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            <small class="text-muted">Recommended: 150-160 characters. Current: <span id="meta_description_count">0</span></small>
                                            @error('meta_description')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>Meta Keywords <small class="text-muted">(Comma separated)</small></label>
                                            <input type="text" 
                                                   name="meta_keywords" 
                                                   class="form-control" 
                                                   value="{{ old('meta_keywords') }}"
                                                   placeholder="blog, news, berifmedia, etc.">
                                            @error('meta_keywords')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>Meta Image <small class="text-muted">(For search engines)</small></label>
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
                                                   value="{{ old('og_title') }}">
                                            @error('og_title')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>OG Description</label>
                                            <textarea name="og_description" 
                                                      class="form-control" 
                                                      rows="3">{{ old('og_description') }}</textarea>
                                            @error('og_description')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>OG Image</label>
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
                                                   value="{{ old('twitter_title') }}">
                                            @error('twitter_title')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>Twitter Description</label>
                                            <textarea name="twitter_description" 
                                                      class="form-control" 
                                                      rows="3">{{ old('twitter_description') }}</textarea>
                                            @error('twitter_description')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>Twitter Image</label>
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
                                                   value="{{ old('canonical_url') }}"
                                                   placeholder="https://example.com/original-post">
                                            @error('canonical_url')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>Schema Markup <small class="text-muted">(JSON-LD)</small></label>
                                            <textarea name="schema_markup" 
                                                      class="form-control" 
                                                      rows="5">{{ old('schema_markup') }}</textarea>
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
                                                   {{ old('no_index') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="no_index">No Index (Hide from search engines)</label>
                                        </div>

                                        <div class="form-check mt-2">
                                            <input type="checkbox" 
                                                   name="no_follow" 
                                                   class="form-check-input" 
                                                   id="no_follow"
                                                   value="1"
                                                   {{ old('no_follow') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="no_follow">No Follow (Don't follow links on this page)</label>
                                        </div>
                                    </div>

                                    <!-- Preview Tab -->
                                    <div class="tab-pane" id="preview-seo" role="tabpanel">
                                        <h6 class="mb-3">Google Search Preview</h6>
                                        <div class="google-preview p-3 border rounded bg-light">
                                            <div class="text-primary" style="font-size: 20px;" id="preview-title">
                                                {{ old('meta_title') ?? old('title') ?? 'Post Title' }}
                                            </div>
                                            <div class="text-success" style="font-size: 14px;" id="preview-url">
                                                {{ url('/') }}/posts/{{ Str::slug(old('title', 'sample-post')) }}
                                            </div>
                                            <div class="text-muted" style="font-size: 14px;" id="preview-description">
                                                {{ old('meta_description') ?? 'Post description will appear here...' }}
                                            </div>
                                        </div>

                                        <h6 class="mt-4 mb-3">Facebook/Open Graph Preview</h6>
                                        <div class="facebook-preview border rounded" style="max-width: 500px;">
                                            <div class="p-3 bg-light border-bottom">
                                                <strong>BerifMedia</strong> · 1 hr
                                            </div>
                                            <div id="preview-og-image" class="bg-secondary" style="height: 200px; background-size: cover; background-position: center;"></div>
                                            <div class="p-3">
                                                <div class="text-muted small" id="preview-og-url">berifmedia.com</div>
                                                <div class="font-weight-bold" id="preview-og-title">{{ old('og_title') ?? old('meta_title') ?? old('title') ?? 'Post Title' }}</div>
                                                <div class="text-muted small" id="preview-og-description">{{ old('og_description') ?? old('meta_description') ?? 'Post description...' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap mt-4">
                            <button type="submit" class="btn btn-primary">Create Post</button>
                            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Back</a>
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

    // Initialize counters
    document.addEventListener('DOMContentLoaded', function() {
        const metaTitle = document.getElementById('meta_title');
        const metaDesc = document.getElementById('meta_description');
        
        if (metaTitle) {
            document.getElementById('meta_title_count').textContent = metaTitle.value.length;
        }
        if (metaDesc) {
            document.getElementById('meta_description_count').textContent = metaDesc.value.length;
        }
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
        const title = document.querySelector('[name="meta_title"]')?.value || document.querySelector('[name="title"]')?.value || 'Post Title';
        const description = document.querySelector('[name="meta_description"]')?.value || 'Post description will appear here...';
        
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
        
        // Update URL preview
        const titleSlug = document.querySelector('[name="title"]')?.value || 'sample-post';
        const urlPreview = document.getElementById('preview-url');
        if (urlPreview) {
            urlPreview.textContent = '{{ url('/') }}/posts/' + titleSlug.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        }
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
</style>
@endpush

@endsection