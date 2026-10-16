{{--
    Shared category form used by admin.categories.create and admin.categories.edit.
    @param \App\Models\Category|null $category  Present when editing
--}}
@php $editingCategory = isset($category) && $category; @endphp

<div class="ad-card" style="max-width: 720px;">
    <form action="{{ $editingCategory ? route('admin.categories.update', $category->id) : route('admin.categories.store') }}" method="POST">
        @csrf
        @if($editingCategory)
            @method('PUT')
        @endif

        <div class="ad-card__body">
            <div class="ad-field">
                <label for="name" class="ad-label">Name <span class="ad-req">*</span></label>
                <input type="text" name="name" id="name" required maxlength="255"
                       class="ad-input @error('name') is-invalid @enderror"
                       value="{{ old('name', $editingCategory ? $category->name : '') }}" placeholder="e.g. Entertainment">
                <div class="ad-help"><span>The URL slug is generated from the name.</span></div>
                @error('name')
                    <span class="ad-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="ad-field">
                <label for="description" class="ad-label">Description <small>Shown at the top of the category page</small></label>
                <textarea name="description" id="description" rows="4" class="ad-textarea @error('description') is-invalid @enderror"
                          placeholder="What readers will find in this section">{{ old('description', $editingCategory ? $category->description : '') }}</textarea>
                @error('description')
                    <span class="ad-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="ad-card__head" style="border-top: 1px solid var(--ad-line); border-bottom: 0; justify-content: flex-end;">
            <a href="{{ route('admin.categories.index') }}" class="ad-btn ad-btn--ghost">Cancel</a>
            <button type="submit" class="ad-btn ad-btn--primary"><i class="bi bi-check2"></i> {{ $editingCategory ? 'Update category' : 'Create category' }}</button>
        </div>
    </form>
</div>
