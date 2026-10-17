<div>
    <form action="{{ route('user.posts.search') }}" method="GET" class="bm-search__form">
        <i class="bi bi-search"></i>
        <input type="search" name="query" class="bm-search__input" placeholder="Search stories, topics, people…"
               wire:model.live.debounce.300ms="query" required autocomplete="off" aria-label="Search posts">
        <span class="bm-search__spinner" wire:loading wire:target="query" aria-hidden="true"></span>
        <button type="submit" class="bm-btn bm-btn--primary bm-btn--sm">Search</button>
    </form>

    @php $term = trim($query); @endphp

    @if(mb_strlen($term) >= 2)
        <div class="bm-search__results" role="listbox" aria-label="Quick results">
            @forelse($results as $result)
                <a href="{{ route('posts.show', $result) }}" class="bm-search__result" role="option" wire:key="search-result-{{ $result->id }}">
                    <img src="{{ $result->featured_image_1 ? asset('storage/' . $result->featured_image_1) : route('theme.asset', ['img', 'bm-placeholder.svg']) }}"
                         alt="" loading="lazy"
                         onerror="this.onerror=null;this.src='{{ route('theme.asset', ['img', 'bm-placeholder.svg']) }}'">
                    <span>
                        <span class="bm-search__result-title">{{ $result->title }}</span>
                        <span class="bm-search__result-meta">
                            {{ optional($result->category)->name ?? 'Story' }} · {{ $result->created_at->format('M j, Y') }}
                        </span>
                    </span>
                </a>
            @empty
                <p class="bm-search__empty">No stories match “{{ $term }}”.</p>
            @endforelse

            @if($results->isNotEmpty())
                <a href="{{ route('user.posts.search', ['query' => $term]) }}" class="bm-search__all">
                    See all results for “{{ $term }}” <i class="bi bi-arrow-right"></i>
                </a>
            @endif
        </div>
    @else
        <div class="bm-search__hint">Type at least 2 letters to see stories · <kbd>Enter</kbd> for all results · <kbd>Esc</kbd> to close</div>
    @endif
</div>
