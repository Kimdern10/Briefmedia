{{--
    One category block on the homepage: 1 big story + up to 6 smaller ones.
    @param \App\Models\Category $category  With its newest posts loaded
    @param int  $sectionNumber             Shown as "Section 01"
    @param bool $tight                     Less space above (every block except a first one with no Trending above it)
--}}
@php
    $categoryPosts = $category->posts->take(7);
    $leadPost = $categoryPosts->first();
    $otherPosts = $categoryPosts->slice(1);
@endphp

<section class="bm-section bm-section--lazy {{ $tight ? 'bm-section--tight' : '' }}">
    <div class="bm-container">
        <div class="bm-section__head">
            <div>
                <span class="bm-section__eyebrow">Section {{ str_pad($sectionNumber, 2, '0', STR_PAD_LEFT) }}</span>
                <h2 class="bm-section__title">{{ $category->name }}</h2>
            </div>
            <a href="{{ route('category.page', $category->slug) }}" class="bm-link-arrow">
                View all <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="bm-cat-grid {{ $otherPosts->isEmpty() ? 'bm-cat-grid--solo' : '' }}">
            <div class="bm-reveal">
                @include('snippets.site.post-card', [
                    'post' => $leadPost,
                    'variant' => 'lead',
                    'categoryName' => $category->name,
                    'categorySlug' => $category->slug,
                ])
            </div>

            @if($otherPosts->isNotEmpty())
                <div class="bm-cat-grid__rest">
                    @foreach($otherPosts as $post)
                        <div class="bm-reveal" style="transition-delay: {{ $loop->index * 60 }}ms;">
                            @include('snippets.site.post-card', [
                                'post' => $post,
                                'variant' => 'compact',
                                'categoryName' => $category->name,
                                'categorySlug' => $category->slug,
                            ])
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
