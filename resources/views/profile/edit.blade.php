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
    @media (max-width: 860px) {
        .bm-account { grid-template-columns: minmax(0, 1fr); }
        .bm-account__card { position: static; }
        .bm-form-grid { grid-template-columns: minmax(0, 1fr); }
    }
</style>
@endpush

@section('content')
@php
    $profileImage = optional($user->Userprofile)->profile_picture
        ? $user->Userprofile->photoUrl()
        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=e4407f&color=fff&size=240';
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

            <!-- Profile card -->
            <aside class="bm-widget bm-account__card">
                <div class="bm-account__avatar" onclick="document.getElementById('profile-upload').click();" title="Change photo">
                    <img src="{{ $profileImage }}" alt="{{ $user->name }}"
                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=e4407f&color=fff&size=240'">
                    <span><i class="bi bi-camera"></i></span>
                </div>
                <form id="profile-upload-form" action="{{ route('update') }}" method="POST" enctype="multipart/form-data" style="display: none;">
                    @csrf
                    @method('put')
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="file" id="profile-upload" name="profile_picture" accept="image/*" onchange="this.form.submit()">
                </form>

                <h2 class="bm-account__name">{{ $user->name }}</h2>
                <p class="bm-meta" style="justify-content: center; margin-top: 4px;">{{ $user->email }}</p>

                <div class="bm-account__facts">
                    @if(optional($user->Userprofile)->phone)
                        <span><i class="bi bi-telephone"></i> {{ $user->Userprofile->phone }}</span>
                    @endif
                    @if(optional($user->Userprofile)->address)
                        <span><i class="bi bi-geo-alt"></i> {{ $user->Userprofile->address }}</span>
                    @endif
                    <span><i class="bi bi-calendar3"></i> Member since {{ $user->created_at->format('F Y') }}</span>
                </div>
            </aside>

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
                    <h3 class="bm-widget__title"><i class="bi bi-person-circle"></i> Profile information</h3>

                    @if(session('profile_success'))
                        <div class="bm-alert bm-alert--success">{{ session('profile_success') }}</div>
                    @endif

                    <form action="{{ route('update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('put')

                        <div class="bm-form-grid">
                            <div class="bm-field bm-field--full">
                                <label class="bm-label" for="name">Full name</label>
                                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}"
                                       class="bm-input @error('name') is-invalid @enderror" placeholder="Enter your full name">
                                @error('name')
                                    <span class="bm-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="bm-field">
                                <label class="bm-label" for="email">Email address</label>
                                <input id="email" type="email" value="{{ $user->email }}" class="bm-input" readonly>
                            </div>

                            <div class="bm-field">
                                <label class="bm-label" for="phone">Phone number</label>
                                <input id="phone" type="text" name="phone" value="{{ old('phone', $user->Userprofile->phone ?? '') }}"
                                       class="bm-input @error('phone') is-invalid @enderror" placeholder="e.g. 08012345678">
                                @error('phone')
                                    <span class="bm-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="bm-field bm-field--full">
                                <label class="bm-label" for="address">Address</label>
                                <input id="address" type="text" name="address" value="{{ old('address', $user->Userprofile->address ?? '') }}"
                                       class="bm-input @error('address') is-invalid @enderror" placeholder="Enter your address">
                                @error('address')
                                    <span class="bm-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="bm-field bm-field--full">
                                <span class="bm-label">Profile picture</span>
                                <label class="bm-dropzone" for="file-input">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <span>
                                        <span class="file-input-text">Click to upload a new photo</span>
                                        <small>PNG, JPG or GIF, up to 2MB</small>
                                    </span>
                                    <input type="file" name="profile_picture" id="file-input" accept="image/*">
                                </label>
                                @error('profile_picture')
                                    <span class="bm-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="bm-form-actions">
                            <button type="submit" class="bm-btn bm-btn--primary"><i class="bi bi-check2"></i> Save changes</button>
                            <a href="{{ url('/') }}" class="bm-btn bm-btn--ghost">Cancel</a>
                        </div>
                    </form>
                </div>

                <!-- Security tab -->
                <div class="bm-tab-pane bm-widget {{ $showSecurityTab ? 'is-active' : '' }}" id="security-tab" role="tabpanel">
                    <h3 class="bm-widget__title"><i class="bi bi-key"></i> Change password</h3>

                    @if(session('success'))
                        <div class="bm-alert bm-alert--success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('password-update') }}" method="POST">
                        @csrf

                        <div class="bm-form-grid">
                            <div class="bm-field bm-field--full">
                                <label class="bm-label" for="current_password">Current password</label>
                                <input id="current_password" type="password" name="current_password" autocomplete="current-password"
                                       class="bm-input @error('current_password') is-invalid @enderror" placeholder="Enter current password">
                                @error('current_password')
                                    <span class="bm-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="bm-field">
                                <label class="bm-label" for="new_password">New password</label>
                                <input id="new_password" type="password" name="new_password" autocomplete="new-password"
                                       class="bm-input @error('new_password') is-invalid @enderror" placeholder="At least 6 characters">
                                @error('new_password')
                                    <span class="bm-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="bm-field">
                                <label class="bm-label" for="new_password_confirmation">Confirm new password</label>
                                <input id="new_password_confirmation" type="password" name="new_password_confirmation" autocomplete="new-password"
                                       class="bm-input @error('new_password_confirmation') is-invalid @enderror" placeholder="Repeat new password">
                                @error('new_password_confirmation')
                                    <span class="bm-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="bm-form-actions">
                            <button type="submit" class="bm-btn bm-btn--primary"><i class="bi bi-shield-check"></i> Update password</button>
                        </div>
                    </form>
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

document.getElementById('file-input')?.addEventListener('change', function () {
    const label = this.closest('.bm-dropzone').querySelector('.file-input-text');
    if (this.files[0] && label) { label.textContent = this.files[0].name; }
});
</script>
@endpush
