{{--
    Like / unlike toggle.
    @param \App\Models\Post $post
    @param string $class  Button classes (defaults to the small inline action style)
--}}
@php
    $likeButtonClass = $class ?? 'bm-action';
    // Homepage cards bring a like count and the visitor's liked post ids (no per-card queries)
    $likedByMe = auth()->check() && (isset($likedPostIds)
        ? in_array($post->id, $likedPostIds, true)
        : $post->likedByUsers->contains(auth()->id()));
    $likesTotal = $post->liked_by_users_count ?? $post->likedByUsers->count();
@endphp

@auth
    <button type="button"
            class="{{ $likeButtonClass }}"
            x-data="{
                liked: {{ $likedByMe ? 'true' : 'false' }},
                count: {{ $likesTotal }},
                busy: false,
                toggle() {
                    if (this.busy) { return; }
                    this.busy = true;
                    fetch('{{ route('posts.like', $post) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => { this.liked = data.liked; this.count = data.likes_count; })
                    .catch(() => window.bmToast && window.bmToast('Could not update like'))
                    .finally(() => { this.busy = false; });
                }
            }"
            @click="toggle()"
            :class="{ 'is-liked': liked }"
            :aria-pressed="liked.toString()"
            aria-label="Like this post">
        <i class="bi" :class="liked ? 'bi-heart-fill' : 'bi-heart'"></i>
        <span x-text="count">{{ $likesTotal }}</span>
    </button>
@else
    <a href="{{ route('login') }}" class="{{ $likeButtonClass }}" aria-label="Log in to like this post" title="Log in to like">
        <i class="bi bi-heart"></i>
        <span>{{ $likesTotal }}</span>
    </a>
@endauth
