<div class="ad-card ad-profile-card">
    <div class="ad-card__body">
        <label class="ad-profile-photo" title="Change photo">
            <img class="ad-avatar ad-avatar--lg" src="{{ $currentPhotoUrl }}" alt="{{ $user->name }}"
                 wire:loading.class="is-uploading" wire:target="photo"
                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=e4407f&color=fff&size=240'">
            <span class="ad-profile-photo__btn">
                <i class="bi bi-camera" wire:loading.remove wire:target="photo"></i>
                <i class="bi bi-arrow-repeat ad-spin" wire:loading wire:target="photo"></i>
            </span>
            <input type="file" wire:model="photo" accept="image/*" style="display: none;" aria-label="Change profile photo">
        </label>

        @error('photo')
            <span class="ad-error" style="margin-bottom: 8px;">{{ $message }}</span>
        @enderror
        @if($savedMessage)
            <p style="color: var(--ad-green); font-weight: 600; font-size: 13px; margin-bottom: 8px !important;"><i class="bi bi-check-circle-fill"></i> {{ $savedMessage }}</p>
        @endif

        <h2 style="font-size: 19px; font-weight: 700;">{{ $user->name }}</h2>
        <p class="ad-post-cell__sub" style="margin-top: 2px !important;">{{ $user->email }}</p>
        <span class="ad-pill ad-pill--green" style="margin-top: 10px;">Admin</span>
        <p class="ad-post-cell__sub" style="margin-top: 10px !important;">Click the photo to change it</p>

        <div class="ad-profile-facts">
            @if(optional($user->Userprofile)->phone)
                <span><i class="bi bi-telephone"></i> {{ $user->Userprofile->phone }}</span>
            @endif
            @if(optional($user->Userprofile)->address)
                <span><i class="bi bi-geo-alt"></i> {{ $user->Userprofile->address }}</span>
            @endif
            <span><i class="bi bi-calendar3"></i> Member since {{ $user->created_at->format('F Y') }}</span>
            <span><i class="bi bi-newspaper"></i> {{ number_format($user->posts_count ?? \App\Models\Post::where('admin_id', $user->id)->count()) }} posts written</span>
        </div>
    </div>
</div>
