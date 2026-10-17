<div>
    <h3 class="bm-widget__title"><i class="bi bi-bookmark"></i> Saved stories</h3>

    @if($posts->isEmpty())
        <p class="bm-meta" style="margin: 0;">
            Nothing saved yet. Tap <i class="bi bi-bookmark"></i> <strong>Save for later</strong> on any story to keep it here.
        </p>
    @else
        <ul class="bm-mini-list">
            @foreach($posts as $savedPost)
                <li class="bm-mini" wire:key="saved-{{ $savedPost->id }}">
                    <a href="{{ route('posts.show', $savedPost) }}">
                        <img class="bm-mini__img" loading="lazy" alt="{{ $savedPost->title }}"
                             src="{{ $savedPost->featured_image_1 ? asset('storage/' . $savedPost->featured_image_1) : route('theme.asset', ['img', 'bm-placeholder.svg']) }}"
                             onerror="this.onerror=null;this.src='{{ route('theme.asset', ['img', 'bm-placeholder.svg']) }}'">
                    </a>
                    <div style="min-width: 0;">
                        <p class="bm-mini__title"><a href="{{ route('posts.show', $savedPost) }}">{{ $savedPost->title }}</a></p>
                        <div class="bm-meta">
                            @if($savedPost->category)<span>{{ $savedPost->category->name }}</span>@endif
                            <span>Saved {{ $savedPost->pivot->created_at?->diffForHumans() }}</span>
                            <button type="button" class="bm-link-btn" wire:click="remove({{ $savedPost->id }})"
                                    wire:loading.attr="disabled" wire:target="remove({{ $savedPost->id }})">
                                <i class="bi bi-x-lg"></i> Remove
                            </button>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        @if($posts->hasPages())
            <div style="display: flex; justify-content: space-between; margin-top: 14px;">
                <button type="button" class="bm-btn bm-btn--ghost bm-btn--sm" wire:click="previousPage('saved')" @disabled($posts->onFirstPage())>
                    <i class="bi bi-arrow-left"></i> Newer
                </button>
                <button type="button" class="bm-btn bm-btn--ghost bm-btn--sm" wire:click="nextPage('saved')" @disabled(! $posts->hasMorePages())>
                    Older <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        @endif
    @endif
</div>
