@extends('layouts.admin')

@section('title', 'Subscribers')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Subscribers</h1>
        <p class="ad-head__sub">Readers on the newsletter list.</p>
    </div>
    <div class="ad-head__actions">
        <button type="button" class="ad-btn ad-btn--ghost" id="copy-emails"><i class="bi bi-clipboard"></i> Copy emails on this page</button>
        <livewire:admin.newsletter-sender />
    </div>
</div>

<livewire:admin.subscribers-table />
@endsection

@push('scripts')
<script>
document.getElementById('copy-emails').addEventListener('click', function () {
    const emails = Array.from(document.querySelectorAll('[data-email]')).map((el) => el.textContent.trim());
    if (!emails.length) { window.adToast('No emails to copy', 'warning'); return; }
    navigator.clipboard.writeText(emails.join(', '))
        .then(() => window.adToast(emails.length + ' email(s) copied'))
        .catch(() => window.adToast('Could not copy emails', 'error'));
});
</script>
@endpush
