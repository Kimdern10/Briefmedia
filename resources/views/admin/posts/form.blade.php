{{--
    Shared post editor used by admin.posts.create and admin.posts.edit.
    @param \Illuminate\Support\Collection $categories
    @param \App\Models\Post|null          $post  Present when editing
--}}
@php
    $editing = isset($post) && $post;
    $autoSeo = (bool) old('enable_auto_seo', $editing ? ($post->enable_auto_seo ?? true) : true);
    $statusValue = old('status', $editing ? ($post->isScheduled() ? 'scheduled' : $post->status) : 'draft');
    $publishAtValue = old('publish_at', $editing && $post->publish_at ? $post->publish_at->format('Y-m-d\TH:i') : '');
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
                                             onerror="this.onerror=null;this.src='{{ route('theme.asset', ['img', 'bm-placeholder.svg']) }}'">
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

                                @if($slot === 1)
                                    {{-- Caption and credit sit right under the cover photo they describe --}}
                                    <div class="ad-photo-meta">
                                        <div class="ad-field">
                                            <label class="ad-label" for="image_caption"><i class="bi bi-card-text"></i> Cover photo caption <small>Optional</small></label>
                                            <input type="text" name="image_caption" id="image_caption" maxlength="255"
                                                   class="ad-input @error('image_caption') is-invalid @enderror"
                                                   value="{{ old('image_caption', $editing ? $post->image_caption : '') }}"
                                                   placeholder="Governor Soludo at Eke Awka market on Monday">
                                            @error('image_caption') <span class="ad-error">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="ad-field">
                                            <label class="ad-label" for="image_credit"><i class="bi bi-camera"></i> Photo credit <small>Optional</small></label>
                                            <input type="text" name="image_credit" id="image_credit" maxlength="120"
                                                   class="ad-input @error('image_credit') is-invalid @enderror"
                                                   value="{{ old('image_credit', $editing ? $post->image_credit : '') }}"
                                                   placeholder="BriefMedia / Chioma Agbomma">
                                            @error('image_credit') <span class="ad-error">{{ $message }}</span> @enderror
                                        </div>
                                        <p class="ad-photo-meta__hint">Shown under the cover photo on the article page.</p>
                                    </div>
                                @endif
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
                            <textarea name="schema_markup" id="schema_markup" rows="6" class="ad-textarea" style="font-family: ui-monospace, Consolas, monospace; font-size: 12.5px;">{{ old('schema_markup', $editing ? $post->customSchemaMarkup() : '') }}</textarea>
                            <div class="ad-help"><span>Leave empty: Google News (NewsArticle) data is added to every story automatically.</span></div>
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
                            <label class="ad-status-option" style="grid-column: 1 / -1;">
                                <input type="radio" name="status" value="scheduled" {{ $statusValue === 'scheduled' ? 'checked' : '' }}>
                                <span><i class="bi bi-calendar-event"></i> Schedule for later</span>
                            </label>
                        </div>
                        @error('status') <span class="ad-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="ad-field" id="publish-at-field" style="{{ $statusValue === 'scheduled' ? '' : 'display: none;' }}">
                        <label class="ad-label" for="publish_at">Go live on</label>
                        <input type="datetime-local" name="publish_at" id="publish_at" value="{{ $publishAtValue }}"
                               min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                               class="ad-input @error('publish_at') is-invalid @enderror">
                        <div class="ad-help"><span>Nigeria time ({{ config('app.timezone') }}). The post publishes itself at this time.</span></div>
                        @error('publish_at') <span class="ad-error">{{ $message }}</span> @enderror
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

                    <div class="ad-field">
                        <label class="ad-label" for="tags">Tags <small>Optional</small></label>
                        <input type="text" name="tags" id="tags" maxlength="500" list="tag-suggestions" autocomplete="off"
                               class="ad-input @error('tags') is-invalid @enderror"
                               value="{{ old('tags', $editing ? $post->tagList() : '') }}"
                               placeholder="Soludo, ASUU, Super Eagles">
                        <datalist id="tag-suggestions">
                            @foreach(\App\Models\Tag::orderBy('name')->limit(200)->pluck('name') as $tagName)
                                <option value="{{ $tagName }}">
                            @endforeach
                        </datalist>
                        <div class="ad-help"><span>Separate with commas. Each tag gets its own page readers can browse.</span></div>
                        @error('tags') <span class="ad-error">{{ $message }}</span> @enderror
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

            @php
                $breakingChecked = (bool) old('is_breaking', $editing ? $post->isBreaking() : false);
                $headlineChecked = (bool) old('is_headline', $editing ? $post->isHeadline() : false);
            @endphp
            <div class="ad-card">
                <div class="ad-card__head"><h3 class="ad-card__title"><i class="bi bi-megaphone"></i> News options</h3></div>
                <div class="ad-card__body">
                    <div class="ad-field">
                        <label class="ad-label" for="author_name">Written by <small>Optional</small></label>
                        <input type="text" name="author_name" id="author_name" maxlength="120"
                               class="ad-input @error('author_name') is-invalid @enderror"
                               value="{{ old('author_name', $editing ? $post->author_name : '') }}"
                               placeholder="{{ auth()->user()->name }}">
                        <div class="ad-help"><span>Shown as “Posted by …”. Leave empty to use your name.</span></div>
                        @error('author_name') <span class="ad-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="ad-field">
                        <label class="ad-check" for="is_headline">
                            <input type="hidden" name="is_headline" value="0">
                            <input type="checkbox" name="is_headline" id="is_headline" value="1" {{ $headlineChecked ? 'checked' : '' }}>
                            <span>
                                <strong>Headline news</strong>
                                <small>
                                    @if($editing && $post->isHeadline())
                                        Headline until {{ $post->headline_until->format('j M, g:i A') }}. Untick to stop now.
                                    @else
                                        Show first in the big homepage slider for {{ \App\Models\Post::HEADLINE_HOURS / 24 }} day
                                    @endif
                                </small>
                            </span>
                        </label>
                    </div>

                    <div class="ad-field">
                        <label class="ad-check" for="is_breaking">
                            <input type="hidden" name="is_breaking" value="0">
                            <input type="checkbox" name="is_breaking" id="is_breaking" value="1" {{ $breakingChecked ? 'checked' : '' }}>
                            <span>
                                <strong style="color: var(--ad-red);">Breaking news</strong>
                                <small>
                                    @if($editing && $post->isBreaking())
                                        Breaking until {{ $post->breaking_until->format('j M, g:i A') }}. Untick to stop now.
                                    @else
                                        Red “Breaking” bar at the top of every page for {{ \App\Models\Post::BREAKING_HOURS / 24 }} day
                                    @endif
                                </small>
                            </span>
                        </label>
                    </div>

                    <div class="ad-field">
                        <label class="ad-label" for="video_url">Video link <small>Optional</small></label>
                        <input type="url" name="video_url" id="video_url" maxlength="500"
                               class="ad-input @error('video_url') is-invalid @enderror"
                               value="{{ old('video_url', $editing ? $post->video_url : '') }}"
                               placeholder="https://www.youtube.com/watch?v=…">
                        <div class="ad-help"><span>YouTube, Facebook or Vimeo. The video plays at the top of the story.</span></div>
                        @error('video_url') <span class="ad-error">{{ $message }}</span> @enderror
                        @if($editing && $post->videoEmbedUrl())
                            <div class="ad-video-preview">
                                <iframe src="{{ $post->videoEmbedUrl() }}" title="Video preview" loading="lazy"
                                        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        @endif
                    </div>

                    @unless($editing)
                        <div class="ad-field">
                            <label class="ad-check" for="is_live">
                                <input type="hidden" name="is_live" value="0">
                                <input type="checkbox" name="is_live" id="is_live" value="1" {{ old('is_live') ? 'checked' : '' }}>
                                <span>
                                    <strong>Live coverage</strong>
                                    <small>For elections, matches or budget day. After saving, post timestamped updates from the edit page.</small>
                                </span>
                            </label>
                        </div>
                    @endunless

                    @if($editing)
                        <div class="ad-field">
                            <div class="ad-help" style="margin: 0;">
                                <span>
                                    <a href="#live-coverage"><i class="bi bi-broadcast-pin"></i> Live coverage</a> and
                                    <a href="#election-results"><i class="bi bi-check2-square"></i> Election results</a>
                                    are at the bottom of this page.
                                </span>
                            </div>
                        </div>

                        <div class="ad-field">
                            <label class="ad-label" for="correction_note">Correction note <small>Optional</small></label>
                            <textarea name="correction_note" id="correction_note" rows="2" maxlength="1000"
                                      class="ad-textarea @error('correction_note') is-invalid @enderror" style="min-height: 64px;"
                                      placeholder="An earlier version of this story gave the wrong date for the meeting.">{{ old('correction_note', $post->correction_note) }}</textarea>
                            <div class="ad-help"><span>Shown at the end of the story. Editing a published story also shows “Updated …” under the title.</span></div>
                            @error('correction_note') <span class="ad-error">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
            </div>

            @php
                $existingPoll = $editing ? $post->poll()->with('options')->first() : null;
            @endphp
            <div class="ad-card">
                <div class="ad-card__head"><h3 class="ad-card__title"><i class="bi bi-bar-chart"></i> Reader poll <small class="ad-post-cell__sub" style="font-weight: 500;">Optional</small></h3></div>
                <div class="ad-card__body">
                    <div class="ad-field">
                        <label class="ad-label" for="poll_question">Question</label>
                        <input type="text" name="poll_question" id="poll_question" maxlength="200"
                               class="ad-input @error('poll_question') is-invalid @enderror"
                               value="{{ old('poll_question', optional($existingPoll)->question) }}"
                               placeholder="Should the fuel subsidy come back?">
                        @error('poll_question') <span class="ad-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="ad-field" style="margin-bottom: 0;">
                        <label class="ad-label" for="poll_options">Answers <small>one per line, 2 to {{ \App\Models\Poll::MAX_OPTIONS }}</small></label>
                        <textarea name="poll_options" id="poll_options" rows="4" maxlength="1000"
                                  class="ad-textarea @error('poll_options') is-invalid @enderror"
                                  placeholder="Yes&#10;No&#10;Not sure">{{ old('poll_options', $existingPoll ? $existingPoll->options->pluck('label')->implode("\n") : '') }}</textarea>
                        <div class="ad-help">
                            <span>
                                @if($existingPoll)
                                    {{ number_format($existingPoll->totalVotes()) }} {{ Str::plural('vote', $existingPoll->totalVotes()) }} so far. Clear the question to remove the poll.
                                @else
                                    Shown inside the story. Leave empty for no poll.
                                @endif
                            </span>
                        </div>
                        @error('poll_options') <span class="ad-error">{{ $message }}</span> @enderror
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

    // Show the date picker only when "Schedule for later" is chosen
    const publishAtField = document.getElementById('publish-at-field');
    form.querySelectorAll('input[name=status]').forEach((radio) => radio.addEventListener('change', () => {
        const scheduled = form.querySelector('input[name=status]:checked').value === 'scheduled';
        publishAtField.style.display = scheduled ? '' : 'none';
        if (scheduled) { document.getElementById('publish_at').focus(); }
    }));
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
        const statusChoice = form.querySelector('input[name=status]:checked');
        if (statusChoice && statusChoice.value === 'scheduled' && !document.getElementById('publish_at').value) {
            problems.push('Pick the date and time to publish.');
        }
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
