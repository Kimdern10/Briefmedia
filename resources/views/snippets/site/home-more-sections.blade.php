{{-- The homepage's lower category blocks, fetched by bm-site.js as the reader scrolls towards them (/?part=more, see UserController::blade) --}}
@foreach ($categoriesWithPosts->slice(\App\Http\Controllers\UserController::HOME_SECTIONS_WITH_PAGE) as $category)
    @include('snippets.site.home-category-section', [
        'category' => $category,
        'sectionNumber' => \App\Http\Controllers\UserController::HOME_SECTIONS_WITH_PAGE + $loop->iteration,
        'tight' => true,
    ])
@endforeach
