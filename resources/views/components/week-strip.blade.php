{{-- <x-week-strip mode="dots" mine />                          "Meciurile mele": a coloured dot on the days with my matches
     <x-week-strip mode="count" city="chisinau" selected="…" />  create form: how many matches a day already has in the city
     Seven days starting today (Figma "Date Strip"), then the next seven, up to six months ahead. Tapping a day dispatches 'week-day' {date};
     the strip follows 'date-picked' {date} and 'week-city' (city slug) from the page.
     Logic: public/assets/js/week-strip.js · Data: GET /calendar/days (one request per month the week touches). --}}
@props(['mode' => 'dots', 'city' => null, 'mine' => false, 'selected' => null])
@php
    $sports = \App\Models\Sport::orderBy('name')->get();
    $config = [
        'today' => today()->toDateString(),
        'locale' => app()->getLocale(),
        'city' => $city,
        'mine' => (bool) $mine,
        'mode' => $mode,
        'selected' => $selected,
        'sportVars' => $sports->mapWithKeys(fn ($s) => [$s->slug => $s->cssVar()]),
        't' => ['count' => __('calendar.count')],
    ];
@endphp

@once
    @push('scripts')
        <script src="{{ \App\Support\Asset::url('assets/js/week-strip.js') }}"></script>
    @endpush
@endonce

<div {{ $attributes->class(['week-strip', 'week-strip--'.$mode]) }} x-data="weekStrip(@js($config))" data-url="{{ route('calendar.days') }}"
     x-on:date-picked.window="follow($event.detail.date)" x-on:week-city.window="setCity($event.detail)">
    <div class="week-strip__head">
        <span class="t-footnote t-secondary" x-text="rangeLabel" aria-live="polite"></span>
        <span class="week-strip__nav">
            <button type="button" class="week-strip__btn" x-on:click="shift(-1)" :disabled="!canPrev" aria-label="{{ __('components.week.prev') }}"><x-icon.arrow-left /></button>
            <button type="button" class="week-strip__btn" x-on:click="shift(1)" :disabled="!canNext" aria-label="{{ __('components.week.next') }}"><x-icon.arrow-right /></button>
        </span>
    </div>
    <div class="week-strip__days" role="group" aria-label="{{ __('components.week.label') }}">
        <template x-for="d in days" :key="d.iso">
            <button type="button" class="week-strip__day" :class="{ 'is-selected': d.iso === selected, 'is-today': d.iso === today }"
                    :disabled="d.past" :aria-pressed="d.iso === selected ? 'true' : 'false'" :aria-label="d.label" x-on:click="pick(d.iso)">
                <span class="week-strip__wd" x-text="d.wd"></span>
                <span class="week-strip__num tabular" x-text="d.num"></span>
                <span class="week-strip__marks" aria-hidden="true">
                    <template x-if="mode === 'dots'">
                        <span class="week-strip__dots"><template x-for="s in d.sports" :key="s"><i :style="`background: var(${vars[s] || '--text-2'})`"></i></template></span>
                    </template>
                    <template x-if="mode === 'count' && d.count">
                        <span class="week-strip__count tabular" x-text="d.count"></span>
                    </template>
                </span>
            </button>
        </template>
    </div>
</div>
