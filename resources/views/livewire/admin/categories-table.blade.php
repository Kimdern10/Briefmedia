<div class="ad-card">
    <div class="ad-card__head">
        <div class="ad-toolbar" style="width: 100%;">
            <label class="ad-search">
                <i class="bi bi-search"></i>
                <input type="search" wire:model.live.debounce.350ms="search" placeholder="Search categories…" aria-label="Search categories">
            </label>
            <span class="ad-spinner" wire:loading aria-hidden="true"></span>
        </div>
    </div>

    <div class="ad-table-wrap" wire:loading.class="is-loading" wire:target="search,gotoPage,nextPage,previousPage">
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="ad-hide-sm">Description</th>
                    <th class="ad-num ad-hide-sm">Posts</th>
                    <th class="ad-hide-sm">{{ $mode === 'trash' ? 'Deleted' : 'Created' }}</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr wire:key="category-row-{{ $category->id }}">
                        <td>
                            <div class="ad-person">
                                <span class="ad-initial">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $category->name }}</strong>
                                    <div class="ad-post-cell__sub">/category/{{ $category->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="ad-hide-sm" style="color: var(--ad-muted); max-width: 420px;">{{ $category->description ? Str::limit($category->description, 80) : 'No description' }}</td>
                        <td class="ad-num ad-hide-sm">{{ number_format($category->posts_count) }}</td>
                        <td class="ad-hide-sm" style="white-space: nowrap;">
                            {{ $mode === 'trash' ? optional($category->deleted_at)->format('j M Y, h:i A') : $category->created_at->format('d M Y') }}
                        </td>
                        <td>
                            <div class="ad-table__actions">
                                @if($mode === 'active')
                                    <a href="{{ route('category.page', $category->slug) }}" target="_blank" class="ad-btn ad-btn--ghost ad-btn--sm ad-btn--icon" title="View on site"><i class="bi bi-box-arrow-up-right"></i></a>
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="ad-btn ad-btn--ghost ad-btn--sm ad-btn--icon" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <button type="button" class="ad-btn ad-btn--danger ad-btn--sm ad-btn--icon" title="Move to trash"
                                            x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.trash({{ $category->id }}))"
                                            data-confirm-text="Move “{{ $category->name }}” to the trash?">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                @else
                                    <button type="button" class="ad-btn ad-btn--success ad-btn--sm" wire:click="restore({{ $category->id }})">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                    </button>
                                    <button type="button" class="ad-btn ad-btn--danger ad-btn--sm"
                                            x-data x-on:click="bmConfirm($el.dataset).then(ok => ok && $wire.forceDelete({{ $category->id }}))"
                                            data-confirm-text="Delete “{{ $category->name }}” permanently? This cannot be undone.">
                                        <i class="bi bi-x-octagon"></i> <span class="ad-hide-sm">Delete forever</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="ad-table-empty">
                            @if($search !== '')
                                <i class="bi bi-funnel"></i>No categories match “{{ $search }}”.
                            @elseif($mode === 'trash')
                                <i class="bi bi-trash3"></i>Trash is empty
                            @else
                                <i class="bi bi-folder2-open"></i>No categories yet
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $categories->links() }}
</div>
