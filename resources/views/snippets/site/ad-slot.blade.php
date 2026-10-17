{{--
    Ad or sponsor code for one place on the site (Admin → Ads). Shows nothing when switched off.
    @param string $placement  One of \App\Models\AdSlot::PLACEMENTS
--}}
@php $adCode = \App\Models\AdSlot::codeFor($placement); @endphp
@if($adCode)
    <aside class="bm-ad bm-ad--{{ str_replace('_', '-', $placement) }}" aria-label="Advertisement">
        <span class="bm-ad__label">Advertisement</span>
        {!! $adCode !!}
    </aside>
@endif
