<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\AdminComponent;
use App\Models\AdSlot;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Admin → Ads: paste AdSense or sponsor code for each place on the site and switch it on or off.
 */
class AdSlotsManager extends Component
{
    use AdminComponent;

    /** @var array<string, string> */
    public array $codes = [];

    /** @var array<string, bool> */
    public array $active = [];

    public function mount(): void
    {
        $saved = AdSlot::all()->keyBy('placement');

        foreach (array_keys(AdSlot::PLACEMENTS) as $placement) {
            $this->codes[$placement] = (string) optional($saved->get($placement))->code;
            $this->active[$placement] = (bool) optional($saved->get($placement))->is_active;
        }
    }

    public function save(string $placement): void
    {
        abort_unless(array_key_exists($placement, AdSlot::PLACEMENTS), 404);

        $this->validate(["codes.{$placement}" => 'nullable|string|max:20000'], [], ["codes.{$placement}" => 'ad code']);

        $code = trim((string) ($this->codes[$placement] ?? ''));
        $isActive = (bool) ($this->active[$placement] ?? false) && $code !== '';

        AdSlot::updateOrCreate(['placement' => $placement], ['code' => $code ?: null, 'is_active' => $isActive]);
        $this->active[$placement] = $isActive;

        $this->toast(AdSlot::PLACEMENTS[$placement]['label'].($isActive ? ' is live.' : ' saved (switched off).'));
    }

    public function toggle(string $placement): void
    {
        abort_unless(array_key_exists($placement, AdSlot::PLACEMENTS), 404);

        if (trim((string) ($this->codes[$placement] ?? '')) === '') {
            $this->active[$placement] = false;
            $this->toast('Paste the ad code first.', 'warning');

            return;
        }

        $this->active[$placement] = ! ($this->active[$placement] ?? false);
        $this->save($placement);
    }

    public function render(): View
    {
        return view('livewire.admin.ad-slots-manager', ['placements' => AdSlot::PLACEMENTS]);
    }
}
