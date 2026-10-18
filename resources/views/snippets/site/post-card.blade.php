{{--
    Post card.
    @param \App\Models\Post $post
    @param string      $variant       'default' | 'lead' | 'compact'
    @param string|null $categoryName  Pass when the category is already known, to skip a lookup
--}}
@php
    $cardVariant = $variant ?? 'default';
    $cardCategory = $categoryName ?? optional($post->category)->name;
    $cardCategorySlug = $categorySlug ?? optional($post->category)->slug;
@endphp

<article class="bm-card bm-card--{{ $cardVariant }}">
    <a href="{{ route('posts.show', $post) }}" class="bm-card__media" aria-hidden="true" tabindex="-1">
        <img src="{{ $post->featured_image_1 ? asset('storage/' . $post->featured_image_1) : route('theme.asset', ['img', 'bm-placeholder.svg']) }}"
             alt="{{ $post->title }}" loading="lazy"
             onerror="this.onerror=null;this.src='{{ route('theme.asset', ['img', 'bm-placeholder.svg']) }}'">
        @if($post->video_url)
            <span class="bm-card__play"><i class="bi bi-play-fill"></i></span>
        @endif
    </a>

    <div class="bm-card__body">
        <div class="bm-meta">
            @if($post->isLive())
                <span class="bm-chip bm-chip--live"><span class="bm-live__dot"></span> Live</span>
            @endif
            @if($post->isBreaking())
                <span class="bm-chip bm-chip--breaking">Breaking</span>
            @endif
            @if($cardCategory)
                @if($cardCategorySlug)
                    <a href="{{ route('category.page', $cardCategorySlug) }}" class="bm-chip">{{ $cardCategory }}</a>
                @else
                    <span class="bm-chip">{{ $cardCategory }}</span>
                @endif
            @endif
            @if($cardVariant !== 'compact')
                <span><i class="bi bi-clock"></i> {{ $post->reading_time }}</span>
            @endif
        </div>

        <h3 class="bm-card__title">
            <a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
        </h3>

        <p class="bm-posted-by">
            Posted by <a href="{{ $post->authorUrl() }}" class="bm-author-link"><strong>{{ $post->authorDisplayName() }}</strong></a>
            <span aria-hidden="true">·</span>
            <time datetime="{{ $post->created_at->toDateString() }}">{{ $post->created_at->format('M j, Y') }}</time>
        </p>

        @if($post->excerpt)
            <p class="bm-card__excerpt">
                {{ Str::limit($post->excerpt, $cardVariant === 'lead' ? 220 : 120) }}
                <a href="{{ route('posts.show', $post) }}">Read more →</a>
            </p>
        @endif

        <div class="bm-card__foot">
            <div class="bm-card__actions">
                <span class="bm-action" title="Views" style="cursor: default;"><i class="bi bi-eye"></i> {{ number_format($post->views ?? 0) }}</span>
                <a href="{{ route('posts.show', $post) }}#comments" class="bm-action" title="Comments"><i class="bi bi-chat"></i> {{ $post->comments_count ?? 0 }}</a>
                @include('snippets.site.like', ['post' => $post])
                @include('snippets.site.share', ['post' => $post, 'lazy' => true])
            </div>
        </div>
    </div>
</article>
