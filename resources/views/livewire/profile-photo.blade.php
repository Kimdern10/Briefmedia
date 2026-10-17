<aside class="bm-widget bm-account__card">
    <label class="bm-account__avatar" title="Change photo">
        <img src="{{ $currentPhotoUrl }}" alt="{{ $user->name }}" wire:loading.class="is-uploading" wire:target="photo"
             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=e4407f&color=fff&size=240'">
        <span><i class="bi bi-camera" wire:loading.remove wire:target="photo"></i><i class="bi bi-arrow-repeat bm-spin" wire:loading wire:target="photo"></i></span>
        <input type="file" wire:model="photo" accept="image/*" class="bm-visually-hidden" aria-label="Change profile photo">
    </label>

    @error('photo')
        <p class="bm-error" style="text-align: center; margin: -6px 0 10px;">{{ $message }}</p>
    @enderror
    @if($savedMessage)
        <p class="bm-account__saved" role="status"><i class="bi bi-check-circle-fill"></i> {{ $savedMessage }}</p>
    @endif

    <h2 class="bm-account__name">{{ $user->name }}</h2>
    <p class="bm-meta" style="justify-content: center; margin-top: 4px;">{{ $user->email }}</p>
    <p class="bm-meta" style="justify-content: center; margin-top: 6px; font-size: 12px;">Tap the photo to change it</p>

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
