<div class="ad-grid ad-grid--2 ad-ads">
    @foreach($placements as $placement => $info)
        <div class="ad-card" wire:key="ad-{{ $placement }}">
            <div class="ad-card__head">
                <h3 class="ad-card__title"><i class="bi bi-badge-ad"></i> {{ $info['label'] }}</h3>
                <label class="ad-switch" title="{{ ($active[$placement] ?? false) ? 'Showing on the site' : 'Hidden' }}">
                    <input type="checkbox" @checked($active[$placement] ?? false) wire:click.prevent="toggle('{{ $placement }}')">
                    <span class="ad-switch__track"></span>
                    <span>{{ ($active[$placement] ?? false) ? 'On' : 'Off' }}</span>
                </label>
            </div>
            <div class="ad-card__body">
                <p class="ad-post-cell__sub" style="margin: 0 0 10px;">{{ $info['hint'] }}</p>
                <textarea class="ad-textarea ad-ads__code @error('codes.'.$placement) is-invalid @enderror" rows="6" spellcheck="false"
                          wire:model="codes.{{ $placement }}"
                          placeholder="Paste your AdSense or sponsor code here, e.g. &lt;ins class=&quot;adsbygoogle&quot; …&gt;&lt;/ins&gt;"></textarea>
                @error('codes.'.$placement) <span class="ad-error">{{ $message }}</span> @enderror
                <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                    <button type="button" class="ad-btn ad-btn--primary ad-btn--sm" wire:click="save('{{ $placement }}')"
                            wire:loading.attr="disabled" wire:target="save('{{ $placement }}')">
                        <i class="bi bi-check2"></i> Save
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>
