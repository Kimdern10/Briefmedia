@extends('layouts.admin')

@section('title', 'Subscribers')

@section('content')
<div class="ad-head">
    <div>
        <h1 class="ad-head__title">Subscribers</h1>
        <p class="ad-head__sub">{{ $subscribers->total() }} {{ Str::plural('reader', $subscribers->total()) }} on the newsletter list.</p>
    </div>
    <div class="ad-head__actions">
        <button type="button" class="ad-btn ad-btn--ghost" id="copy-emails"><i class="bi bi-clipboard"></i> Copy emails on this page</button>
        <a href="{{ route('admin.send-newsletter') }}" class="ad-btn ad-btn--accent"><i class="bi bi-send"></i> Send newsletter</a>
    </div>
</div>

<div class="ad-card">
    <div class="ad-card__head">
        <label class="ad-search">
            <i class="bi bi-search"></i>
            <input type="search" placeholder="Filter by email…" data-table-filter="#subscribers-table" aria-label="Filter subscribers">
        </label>
    </div>
    <div class="ad-table-wrap">
        <table class="ad-table" id="subscribers-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Subscribed</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscribers as $subscriber)
                    <tr data-row>
                        <td>
                            <div class="ad-person">
                                <span class="ad-initial"><i class="bi bi-envelope"></i></span>
                                <strong data-email>{{ $subscriber->email }}</strong>
                            </div>
                        </td>
                        <td style="white-space: nowrap;">{{ $subscriber->created_at->format('j M Y') }} <span class="ad-post-cell__sub">· {{ $subscriber->created_at->diffForHumans() }}</span></td>
                        <td>
                            <div class="ad-table__actions">
                                <form action="{{ route('admin.subscribers.delete', $subscriber->id) }}" method="POST"
                                      data-confirm="{{ $subscriber->email }} will be removed from the newsletter." data-confirm-title="Remove subscriber?" data-confirm-button="Remove">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ad-btn ad-btn--danger ad-btn--sm ad-btn--icon" title="Remove subscriber"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="ad-table-empty"><i class="bi bi-envelope-open"></i>No subscribers yet</td></tr>
                @endforelse
                <tr data-filter-empty hidden><td colspan="3" class="ad-table-empty"><i class="bi bi-funnel"></i>No subscribers match this filter.</td></tr>
            </tbody>
        </table>
    </div>

    {{ $subscribers->links('snippets.site.pagination') }}
</div>
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
