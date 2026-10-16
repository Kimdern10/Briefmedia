{{--
    Share popover (falls back to the native share sheet on touch devices).
    @param \App\Models\Post $post
    @param string $trigger  Trigger button classes
    @param bool   $down     Open the menu below the trigger instead of above
--}}
@php
    $shareUrl = route('posts.show', $post);
    $shareTitle = $post->title;
@endphp

<div class="bm-share" data-url="{{ $shareUrl }}" data-title="{{ $shareTitle }}">
    <button type="button" class="{{ $trigger ?? 'bm-action' }}" data-share-toggle aria-expanded="false" aria-label="Share this post">
        <i class="bi bi-share"></i>@isset($label)<span>{{ $label }}</span>@endisset
    </button>
    <div class="bm-share__menu {{ !empty($down) ? 'bm-share__menu--down' : '' }}">
        <a class="bm-share__option" target="_blank" rel="noopener"
           href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}">
            <i class="bi bi-facebook"></i> Facebook
        </a>
        <a class="bm-share__option" target="_blank" rel="noopener"
           href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareTitle) }}">
            <i class="bi bi-twitter-x"></i> X (Twitter)
        </a>
        <a class="bm-share__option" target="_blank" rel="noopener"
           href="https://www.linkedin.com/sharing/share-offsite?url={{ urlencode($shareUrl) }}">
            <i class="bi bi-linkedin"></i> LinkedIn
        </a>
        <a class="bm-share__option" target="_blank" rel="noopener"
           href="https://wa.me/?text={{ urlencode($shareTitle . ' ' . $shareUrl) }}">
            <i class="bi bi-whatsapp"></i> WhatsApp
        </a>
        <button type="button" class="bm-share__option" data-copy-link="{{ $shareUrl }}">
            <i class="bi bi-link-45deg"></i> Copy link
        </button>
    </div>
</div>
