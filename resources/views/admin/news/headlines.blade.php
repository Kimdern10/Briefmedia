@extends('layouts.admin')

@section('title', 'Headlines')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Daily headlines</h1>
        <p class="ad-head__sub">The 5 latest published stories, formatted for WhatsApp broadcasts and groups. Edit before sharing if you like.</p>
    </div>
</div>

<div class="ad-grid ad-grid--main">
    <div class="ad-card">
        <div class="ad-card__head">
            <h3 class="ad-card__title"><i class="bi bi-broadcast"></i> Message</h3>
            <span class="ad-post-cell__sub"><span id="headline-count">0</span> characters</span>
        </div>
        <div class="ad-card__body">
            <textarea id="headlineText" class="ad-textarea" rows="16" data-counter="headline-count"
                      style="font-family: ui-monospace, Consolas, monospace; font-size: 13px;">{{ $message }}</textarea>
            <div class="ad-toolbar" style="margin-top: 14px;">
                <button type="button" class="ad-btn ad-btn--primary" id="copy-headlines"><i class="bi bi-clipboard"></i> Copy message</button>
                <a href="https://wa.me/?text={{ urlencode($message) }}" target="_blank" rel="noopener" class="ad-btn ad-btn--success" id="share-whatsapp">
                    <i class="bi bi-whatsapp"></i> Share to WhatsApp
                </a>
            </div>
        </div>
    </div>

    <div class="ad-card">
        <div class="ad-card__head"><h3 class="ad-card__title"><i class="bi bi-phone"></i> Preview</h3></div>
        <div class="ad-card__body" style="background: #e5ddd5; border-radius: 0 0 var(--ad-radius) var(--ad-radius);">
            <div id="headline-preview" style="max-width: 320px; margin-left: auto; padding: 10px 12px; border-radius: 10px 0 10px 10px; background: #dcf8c6; color: #111; font-size: 13px; white-space: pre-wrap; word-break: break-word; box-shadow: 0 1px 1px rgba(0,0,0,.12);"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const text = document.getElementById('headlineText');
    const preview = document.getElementById('headline-preview');
    const share = document.getElementById('share-whatsapp');

    const render = () => {
        // WhatsApp-style *bold* rendering for the preview only
        const escaped = text.value.replace(/[&<>]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
        preview.innerHTML = escaped.replace(/\*(.+?)\*/g, '<strong>$1</strong>');
        share.href = 'https://wa.me/?text=' + encodeURIComponent(text.value);
    };
    text.addEventListener('input', render);
    render();

    document.getElementById('copy-headlines').addEventListener('click', () => {
        navigator.clipboard.writeText(text.value)
            .then(() => window.adToast('Headlines copied to clipboard'))
            .catch(() => window.adToast('Could not copy headlines', 'error'));
    });
})();
</script>
@endpush
