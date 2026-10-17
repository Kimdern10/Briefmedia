@extends('layouts.app')

@section('title', 'My account | ' . ($globalSeo->site_name ?? config('app.name')))
@section('auth_page', true)

@push('styles')
<style>
    .bm-account { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 28px; align-items: start; }
    .bm-account__card { position: sticky; top: calc(var(--bm-header-h) + 20px); text-align: center; }
    .bm-account__avatar { position: relative; width: 120px; height: 120px; margin: 0 auto 16px; border-radius: 999px; cursor: pointer; }
    .bm-account__avatar img { width: 100%; height: 100%; border-radius: 999px; object-fit: cover; box-shadow: 0 0 0 4px var(--bm-surface), 0 0 0 5px var(--bm-line); }
    .bm-account__avatar span { position: absolute; right: 2px; bottom: 2px; display: grid; place-items: center; width: 34px; height: 34px; border-radius: 999px; background: var(--bm-ink); color: var(--bm-bg); border: 3px solid var(--bm-surface); font-size: 14px; }
    .bm-account__name { font-family: var(--bm-serif); font-size: 22px; font-weight: 600; }
    .bm-account__facts { display: grid; gap: 8px; margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--bm-line); text-align: left; color: var(--bm-ink-2); font-size: 14px; }
    .bm-account__facts i { width: 20px; color: var(--bm-muted); }
    .bm-tabs { display: inline-flex; gap: 4px; padding: 4px; margin-bottom: 18px; border-radius: 999px; background: var(--bm-surface); border: 1px solid var(--bm-line); }
    .bm-tab { height: 38px; padding: 0 18px; border: 0; border-radius: 999px; background: transparent; color: var(--bm-muted); font-weight: 600; font-size: 14px; cursor: pointer; }
    .bm-tab.is-active { background: var(--bm-ink); color: var(--bm-bg); }
    .bm-tab-pane { display: none; }
    .bm-tab-pane.is-active { display: block; }
    .bm-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 16px; }
    .bm-form-grid .bm-field--full { grid-column: 1 / -1; }
    .bm-input[readonly] { opacity: .65; cursor: not-allowed; }
    .bm-dropzone { display: flex; align-items: center; gap: 14px; padding: 16px; border-radius: 12px; border: 1.5px dashed var(--bm-line); background: var(--bm-bg); cursor: pointer; transition: border-color .2s ease; }
    .bm-dropzone:hover { border-color: var(--bm-accent); }
    .bm-dropzone i { font-size: 24px; color: var(--bm-accent); }
    .bm-dropzone input { display: none; }
    .bm-dropzone small { display: block; color: var(--bm-muted); }
    .bm-form-actions { display: flex; gap: 10px; margin-top: 8px; }
    .bm-account__avatar img { transition: opacity .2s ease; }
    .bm-account__avatar img.is-uploading { opacity: .45; }
    .bm-account__saved { display: flex; align-items: center; justify-content: center; gap: 6px; margin: -4px 0 10px !important; color: var(--bm-success); font-size: 13px; font-weight: 600; }
    .bm-spin { display: inline-block; animation: bm-spin .8s linear infinite; }
    .bm-photo-preview { display: flex; align-items: center; gap: 14px; padding: 12px; border-radius: 12px; border: 1px solid var(--bm-line); background: var(--bm-bg); }
    .bm-photo-preview img { width: 72px; height: 72px; border-radius: 999px; object-fit: cover; object-position: 50% 25%; }
    .bm-photo-preview small { display: block; color: var(--bm-muted); }
    @media (max-width: 860px) {
        .bm-account { grid-template-columns: minmax(0, 1fr); }
        .bm-account__card { position: static; }
        .bm-form-grid { grid-template-columns: minmax(0, 1fr); }
    }
</style>
@endpush

@section('content')
@php
    $showSecurityTab = session('success')
        || $errors->has('current_password')
        || $errors->has('new_password')
        || $errors->has('new_password_confirmation');
@endphp

<main>
    <section class="bm-page-hero">
        <div class="bm-container">
            <nav class="bm-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <i class="bi bi-chevron-right" style="font-size: 10px;"></i>
                <span>My account</span>
            </nav>
            <h1 class="bm-page-hero__title">Hi, {{ Str::before($user->name, ' ') ?: $user->name }}.</h1>
            <p class="bm-page-hero__text">Manage your profile and account security.</p>
        </div>
    </section>

    <section class="bm-section">
        <div class="bm-container bm-account">

            <!-- Profile card (Livewire: tap the photo to upload) -->
            <livewire:profile-photo />

            <!-- Tabs -->
            <div>
                <div class="bm-tabs" role="tablist">
                    <button type="button" class="bm-tab {{ $showSecurityTab ? '' : 'is-active' }}" data-tab="profile" role="tab">
                        <i class="bi bi-person"></i> Profile
                    </button>
                    <button type="button" class="bm-tab {{ $showSecurityTab ? 'is-active' : '' }}" data-tab="security" role="tab">
                        <i class="bi bi-shield-lock"></i> Security
                    </button>
                </div>

                <!-- Profile tab -->
                <div class="bm-tab-pane bm-widget {{ $showSecurityTab ? '' : 'is-active' }}" id="profile-tab" role="tabpanel">
                    <livewire:profile-form />
                </div>

                <!-- Security tab -->
                <div class="bm-tab-pane bm-widget {{ $showSecurityTab ? 'is-active' : '' }}" id="security-tab" role="tabpanel">
                    <livewire:change-password />
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.bm-tab').forEach((button) => {
    button.addEventListener('click', () => {
        document.querySelectorAll('.bm-tab').forEach((b) => b.classList.toggle('is-active', b === button));
        document.querySelectorAll('.bm-tab-pane').forEach((pane) => {
            pane.classList.toggle('is-active', pane.id === button.dataset.tab + '-tab');
        });
    });
});

</script>
@endpush
