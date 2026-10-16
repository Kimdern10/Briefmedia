{{--
    Shared post editor used by admin.posts.create and admin.posts.edit.
    @param \Illuminate\Support\Collection $categories
    @param \App\Models\Post|null          $post  Present when editing
--}}
@php
    $editing = isset($post) && $post;
    $autoSeo = (bool) old('enable_auto_seo', $editing ? ($post->enable_auto_seo ?? true) : true);
    $statusValue = old('status', $editing ? $post->status : 'draft');
    $previewTitle = old('meta_title', $editing ? ($post->meta_title ?? $post->title) : null) ?: old('title', $editing ? $post->title : 'Post title');
    $previewDescription = old('meta_description', $editing ? $post->meta_description : null) ?: 'Post description will appear here…';
    $previewUrl = url('/') . '/posts/' . ($editing ? $post->slug : Str::slug(old('title', 'sample-post')));
    $imageSlots = [
        1 => 'Cover image',
        2 => 'Inline image',
        3 => 'Gallery image',
        4 => 'Gallery image',
    ];
@endphp

<form action="{{ $editing ? route('admin.posts.update', $post->id) : route('admin.posts.store') }}"
      method="POST" enctype="multipart/form-data" id="post-form" novalidate>
    @csrf
    @if($editing)
        @method('PUT')
    @endif

    <div class="ad-grid ad-grid--main">

        <!-- ================= MAIN COLUMN ================= -->
        <div class="ad-stack">
            <div class="ad-card">
                <div class="ad-card__body">
                    <div class="ad-field">
                        <label class="ad-label" for="title">Title <span class="ad-req">*</span></label>
                        <input type="text" name="title" id="title" required maxlength="255"
                               class="ad-input ad-input--lg @error('title') is-invalid @enderror"
                               value="{{ old('title', $editing ? $post->title : '') }}" placeholder="Write a headline that makes people click">
                        @error('title')
                            <span class="ad-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="ad-field">
                        <label class="ad-label" for="excerpt">Excerpt <small>Shown on cards and under the headline</small></label>
                        <textarea name="excerpt" id="excerpt" rows="3" maxlength="{{ $editing ? 500 : 700 }}"
                                  class="ad-textarea @error('excerpt') is-invalid @enderror"
                                  data-counter="excerpt_count" data-recommended="200"
                                  placeholder="A short summary of the story">{{ old('excerpt', $editing ? $post->excerpt : '') }}</textarea>
                        <div class="ad-help"><span>Aim for one or two sentences.</span><span class="ad-counter"><span id="excerpt_count">0</span> chars</span></div>
                        @error('excerpt')
                            <span class="ad-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="ad-field">
                        <label class="ad-label" for="editor">Content <span class="ad-req">*</span></label>
                        <textarea name="content" id="editor" rows="14" required
                                  class="ad-textarea @error('content') is-invalid @enderror">{{ old('content', $editing ? $post->content : '') }}</textarea>
                        @error('content')
                            <span class="ad-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="ad-card">
                <div class="ad-card__head">
                    <h3 class="ad-card__title"><i class="bi bi-images"></i> Images</h3>
                    <span class="ad-post-cell__sub">JPG, PNG or WebP · max 5MB each</span>
                </div>
                <div class="ad-card__body">
                    <div class="ad-uploads">
                        @foreach($imageSlots as $slot => $slotLabel)
                            @php
                                $field = 'featured_image_' . $slot;
                                $current = $editing ? $post->{$field} : null;
                            @endphp
                            <div class="ad-upload">
                                <label class="ad-upload__box {{ $current ? 'has-image' : '' }} @error($field) is-invalid @enderror">
                                    @if($current)
                                        <img src="{{ asset('storage/' . $current) }}" alt=""
                                             onerror="this.onerror=null;this.src='{{ asset('assets/img/bm-placeholder.svg') }}'">
                                    @endif
                                    <span><i class="bi bi-cloud-arrow-up"></i>{{ $current ? 'Replace' : 'Upload' }}</span>
                                    <input type="file" name="{{ $field }}" accept="image/*" {{ $slot === 1 && !$editing ? 'required' : '' }}>
                                </label>
                                <span class="ad-upload__name">{{ $slotLabel }} {!! $slot === 1 ? '<span class="ad-req">*</span>' : '' !!}</span>
                                @if($editing && $current)
                                    <label class="ad-check ad-upload__remove">
                                        <input type="checkbox" name="remove_featured_image_{{ $slot }}" value="1"> Remove image
                                    </label>
                                @endif
                                @error($field)
                                    <span class="ad-error">{{ $message }}</span>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="ad-card" data-tabs>
                <div class="ad-card__head">
                    <h3 class="ad-card__title"><i class="bi bi-search-heart"></i> SEO &amp; social</h3>
                    <label class="ad-switch">
                        <input type="checkbox" name="enable_auto_seo" id="enableAutoSeo" value="1" {{ $autoSeo ? 'checked' : '' }}>
                        <span class="ad-switch__track"></span>
                        Auto-generate
                    </label>
                </div>

                <div class="ad-tabs" role="tablist">
                    <button type="button" class="ad-tab is-active" data-tab="seo-basic">Search</button>
                    <button type="button" class="ad-tab" data-tab="seo-social">Social</button>
                    <button type="button" class="ad-tab" data-tab="seo-advanced">Advanced</button>
                    <button type="button" class="ad-tab" data-tab="seo-preview">Preview</button>
                </div>

                <div class="ad-card__body">
                    <div class="ad-notice ad-notice--info" id="autoSeoNotice" style="{{ $autoSeo ? '' : 'display: none;' }}">
                        <i class="bi bi-magic"></i>
                        <span><strong>Auto SEO is on.</strong> Empty fields are generated from your title and content. You can still override any field.</span>
                    </div>
                    <div class="ad-notice ad-notice--warn" id="manualSeoNotice" style="{{ $autoSeo ? 'display: none;' : '' }}">
                        <i class="bi bi-pencil"></i>
                        <span><strong>Manual SEO mode.</strong> Fill in the fields below yourself.</span>
                    </div>

                    <!-- Search -->
                    <div class="ad-tab-pane is-active" id="seo-basic">
                        <div class="ad-field">
                            <label class="ad-label" for="meta_title">Meta title <small>50–60 characters</small></label>
                            <input type="text" name="meta_title" id="meta_title" maxlength="70" class="ad-input @error('meta_title') is-invalid @enderror"
                                   value="{{ old('meta_title', $editing ? $post->meta_title : '') }}"
                                   data-counter="meta_title_count" data-recommended="60" data-auto-placeholder>
                            <div class="ad-help"><span>Shown as the blue link in Google.</span><span class="ad-counter"><span id="meta_title_count">0</span>/60</span></div>
                            @error('meta_title')
                                <span class="ad-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="ad-field">
                            <label class="ad-label" for="meta_description">Meta description <small>150–160 characters</small></label>
                            <textarea name="meta_description" id="meta_description" rows="3" maxlength="320"
                                      class="ad-textarea @error('meta_description') is-invalid @enderror"
                                      data-counter="meta_description_count" data-recommended="160" data-auto-placeholder>{{ old('meta_description', $editing ? $post->meta_description : '') }}</textarea>
                            <div class="ad-help"><span>The snippet under the link in search results.</span><span class="ad-counter"><span id="meta_description_count">0</span>/160</span></div>
                            @error('meta_description')
                                <span class="ad-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="ad-field">
                            <label class="ad-label" for="meta_keywords">Meta keywords <small>Comma separated</small></label>
                            <input type="text" name="meta_keywords" id="meta_keywords" class="ad-input @error('meta_keywords') is-invalid @enderror"
                                   value="{{ old('meta_keywords', $editing ? $post->meta_keywords : '') }}" placeholder="news, politics, briefmedia" data-auto-placeholder>
                            @error('meta_keywords')
                                <span class="ad-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="ad-field">
                            <label class="ad-label">Meta image <small>For search engines</small></label>
                            @if($editing && $post->meta_image)
                                <div class="ad-current-img">
                                    <img src="{{ asset('storage/' . $post->meta_image) }}" alt="">
                                    <label class="ad-check ad-upload__remove"><input type="checkbox" name="remove_meta_image" value="1"> Remove image</label>
                                </div>
                            @endif
                            <input type="file" name="meta_image" accept="image/*" class="ad-file">
                            @error('meta_image')
                                <span class="ad-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Social -->
                    <div class="ad-tab-pane" id="seo-social">
                        <div class="ad-grid ad-grid--2">
                            <div>
                                <h4 class="ad-label" style="margin-bottom: 12px;"><span><i class="bi bi-facebook" style="color: #1877f2;"></i> Open Graph (Facebook, LinkedIn)</span></h4>
                                <div class="ad-field">
                                    <label class="ad-label" for="og_title">OG title</label>
                                    <input type="text" name="og_title" id="og_title" maxlength="70" class="ad-input" value="{{ old('og_title', $editing ? $post->og_title : '') }}">
                                    @error('og_title') <span class="ad-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="ad-field">
                                    <label class="ad-label" for="og_description">OG description</label>
                                    <textarea name="og_description" id="og_description" rows="3" maxlength="320" class="ad-textarea">{{ old('og_description', $editing ? $post->og_description : '') }}</textarea>
                                    @error('og_description') <span class="ad-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="ad-field">
                                    <label class="ad-label">OG image</label>
                                    @if($editing && $post->og_image)
                                        <div class="ad-current-img">
                                            <img src="{{ asset('storage/' . $post->og_image) }}" alt="">
                                            <label class="ad-check ad-upload__remove"><input type="checkbox" name="remove_og_image" value="1"> Remove image</label>
                                        </div>
                                    @endif
                                    <input type="file" name="og_image" id="og_image" accept="image/*" class="ad-file">
                                    @error('og_image') <span class="ad-error">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <h4 class="ad-label" style="margin-bottom: 12px;"><span><i class="bi bi-twitter-x"></i> Twitter / X card</span></h4>
                                <div class="ad-field">
                                    <label class="ad-label" for="twitter_title">Twitter title</label>
                                    <input type="text" name="twitter_title" id="twitter_title" class="ad-input" value="{{ old('twitter_title', $editing ? $post->twitter_title : '') }}">
                                    @error('twitter_title') <span class="ad-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="ad-field">
                                    <label class="ad-label" for="twitter_description">Twitter description</label>
                                    <textarea name="twitter_description" id="twitter_description" rows="3" class="ad-textarea">{{ old('twitter_description', $editing ? $post->twitter_description : '') }}</textarea>
                                    @error('twitter_description') <span class="ad-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="ad-field">
                                    <label class="ad-label">Twitter image</label>
                                    @if($editing && $post->twitter_image)
                                        <div class="ad-current-img">
                                            <img src="{{ asset('storage/' . $post->twitter_image) }}" alt="">
                                            <label class="ad-check ad-upload__remove"><input type="checkbox" name="remove_twitter_image" value="1"> Remove image</label>
                                        </div>
                                    @endif
                                    <input type="file" name="twitter_image" accept="image/*" class="ad-file">
                                    @error('twitter_image') <span class="ad-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced -->
                    <div class="ad-tab-pane" id="seo-advanced">
                        <div class="ad-field">
                            <label class="ad-label" for="canonical_url">Canonical URL <small>Optional</small></label>
                            <input type="url" name="canonical_url" id="canonical_url" class="ad-input @error('canonical_url') is-invalid @enderror"
                                   value="{{ old('canonical_url', $editing ? $post->canonical_url : '') }}" placeholder="https://example.com/original-post">
                            @error('canonical_url') <span class="ad-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="schema_markup">Schema markup <small>JSON-LD</small></label>
                            <textarea name="schema_markup" id="schema_markup" rows="6" class="ad-textarea" style="font-family: ui-monospace, Consolas, monospace; font-size: 12.5px;">{{ old('schema_markup', $editing ? $post->schema_markup : '') }}</textarea>
                            @error('schema_markup') <span class="ad-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="ad-field">
                            <label class="ad-check" for="no_index">
                                <input type="checkbox" name="no_index" id="no_index" value="1" {{ old('no_index', $editing ? $post->no_index : false) ? 'checked' : '' }}>
                                <span>No index <small>Hide this post from search engines</small></span>
                            </label>
                        </div>
                        <div class="ad-field">
                            <label class="ad-check" for="no_follow">
                                <input type="checkbox" name="no_follow" id="no_follow" value="1" {{ old('no_follow', $editing ? $post->no_follow : false) ? 'checked' : '' }}>
                                <span>No follow <small>Tell search engines not to follow links in this post</small></span>
                            </label>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="ad-tab-pane" id="seo-preview">
                        <h4 class="ad-label" style="margin-bottom: 10px;">Google search</h4>
                        <div class="ad-serp">
                            <div class="ad-serp__url" id="preview-url">{{ $previewUrl }}</div>
                            <div class="ad-serp__title" id="preview-title">{{ $previewTitle }}</div>
                            <div class="ad-serp__desc" id="preview-description">{{ $previewDescription }}</div>
                        </div>

                        <h4 class="ad-label" style="margin: 22px 0 10px;">Facebook / Open Graph</h4>
                        <div class="ad-og">
                            <div class="ad-og__img" id="preview-og-image"
                                 style="{{ $editing && $post->og_image ? 'background-image: url(' . asset('storage/' . $post->og_image) . ');' : '' }}"></div>
                            <div class="ad-og__body">
                                <div class="ad-og__domain">{{ parse_url(url('/'), PHP_URL_HOST) }}</div>
                                <div class="ad-og__title" id="preview-og-title">{{ old('og_title', $editing ? $post->og_title : null) ?: $previewTitle }}</div>
                                <div class="ad-og__desc" id="preview-og-description">{{ old('og_description', $editing ? $post->og_description : null) ?: $previewDescription }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= SIDE COLUMN ================= -->
        <div class="ad-stack" style="position: sticky; top: 84px;">
            <div class="ad-card">
                <div class="ad-card__head"><h3 class="ad-card__title"><i class="bi bi-send"></i> Publish</h3></div>
                <div class="ad-card__body">
                    <div class="ad-field">
                        <span class="ad-label">Status</span>
                        <div class="ad-status-options">
                            <label class="ad-status-option">
                                <input type="radio" name="status" value="draft" {{ $statusValue === 'draft' ? 'checked' : '' }}>
                                <span><i class="bi bi-pencil"></i> Draft</span>
                            </label>
                            <label class="ad-status-option">
                                <input type="radio" name="status" value="published" {{ $statusValue === 'published' ? 'checked' : '' }}>
                                <span><i class="bi bi-globe2"></i> Published</span>
                            </label>
                        </div>
                        @error('status') <span class="ad-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="ad-field">
                        <label class="ad-label" for="category_id">Category <span class="ad-req">*</span></label>
                        <select name="category_id" id="category_id" required class="ad-select @error('category_id') is-invalid @enderror">
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) old('category_id', $editing ? $post->category_id : '') === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="ad-error">{{ $message }}</span> @enderror
                    </div>

                    @if($editing)
                        <div class="ad-help" style="margin: -4px 0 16px;">
                            <span>Created {{ $post->created_at->format('d M Y') }}</span>
                            <span>{{ number_format($post->views ?? 0) }} views</span>
                        </div>
                    @endif

                    <div class="ad-stack" style="gap: 8px;">
                        <button type="submit" class="ad-btn ad-btn--primary" style="width: 100%;">
                            <i class="bi bi-check2"></i> {{ $editing ? 'Update post' : 'Create post' }}
                        </button>
                        @if($editing && $post->status === 'published')
                            <a href="{{ route('posts.show', $post) }}" target="_blank" class="ad-btn ad-btn--ghost" style="width: 100%;"><i class="bi bi-box-arrow-up-right"></i> View on site</a>
                        @endif
                        <a href="{{ route('admin.posts.index') }}" class="ad-btn ad-btn--ghost" style="width: 100%;">Cancel</a>
                    </div>
                </div>
            </div>

            <div class="ad-card">
                <div class="ad-card__body" style="font-size: 13px; color: var(--ad-muted);">
                    <strong style="color: var(--ad-ink);"><i class="bi bi-lightbulb" style="color: var(--ad-amber);"></i> Tips</strong>
                    <ul style="margin: 8px 0 0; padding-left: 18px; display: grid; gap: 4px;">
                        <li>The cover image appears on cards and at the top of the article.</li>
                        <li>The inline image shows before the body; gallery images after it.</li>
                        <li>Keep titles under ~70 characters for search.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
(function () {
    const form = document.getElementById('post-form');
    const contentField = document.getElementById('editor');

    // Rich text editor (the plain textarea stays usable if the CDN is unreachable)
    if (window.CKEDITOR) {
        CKEDITOR.replace('editor', { height: 420, versionCheck: false, removePlugins: 'elementspath', resize_enabled: true });
        contentField.removeAttribute('required');
    }

    form.addEventListener('submit', function (event) {
        if (window.CKEDITOR && CKEDITOR.instances.editor) {
            CKEDITOR.instances.editor.updateElement();
        }

        const problems = [];
        if (!form.title.value.trim()) { problems.push('Add a title.'); }
        if (!contentField.value.trim()) { problems.push('Write some content.'); }
        if (!form.category_id.value) { problems.push('Choose a category.'); }
        const cover = form.querySelector('[name=featured_image_1]');
        if (cover.required && !cover.files.length) { problems.push('Upload a cover image.'); }

        if (problems.length) {
            event.preventDefault();
            window.adToast(problems.join(' '), 'error');
        }
    });

    // Auto SEO toggle
    document.getElementById('enableAutoSeo').addEventListener('change', function () {
        document.getElementById('autoSeoNotice').style.display = this.checked ? '' : 'none';
        document.getElementById('manualSeoNotice').style.display = this.checked ? 'none' : '';
        document.querySelectorAll('[data-auto-placeholder]').forEach((input) => {
            input.placeholder = this.checked ? 'Leave empty to auto-generate' : '';
        });
    });
    document.getElementById('enableAutoSeo').dispatchEvent(new Event('change'));

    // Live search / social previews
    const value = (name) => (form.querySelector('[name="' + name + '"]') || {}).value || '';
    const slugify = (text) => text.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    const editing = @json($editing);

    function updatePreview() {
        const title = value('meta_title') || value('title') || 'Post title';
        const description = value('meta_description') || value('excerpt') || 'Post description will appear here…';
        document.getElementById('preview-title').textContent = title;
        document.getElementById('preview-description').textContent = description;
        document.getElementById('preview-og-title').textContent = value('og_title') || title;
        document.getElementById('preview-og-description').textContent = value('og_description') || description;
        if (!editing) {
            document.getElementById('preview-url').textContent = @json(url('/') . '/posts/') + (slugify(value('title')) || 'sample-post');
        }
    }
    ['title', 'excerpt', 'meta_title', 'meta_description', 'og_title', 'og_description'].forEach((name) => {
        const field = form.querySelector('[name="' + name + '"]');
        field && field.addEventListener('input', updatePreview);
    });

    const ogPreview = document.getElementById('preview-og-image');
    const setOgImage = (file) => { if (file) { ogPreview.style.backgroundImage = 'url(' + URL.createObjectURL(file) + ')'; } };
    document.getElementById('og_image').addEventListener('change', (e) => setOgImage(e.target.files[0]));
    form.querySelector('[name=featured_image_1]').addEventListener('change', (e) => {
        if (!ogPreview.style.backgroundImage) { setOgImage(e.target.files[0]); }
    });
})();
</script>
@endpush
