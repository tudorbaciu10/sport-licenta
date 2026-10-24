@extends('layouts.app')

@section('title', __('rooms.index.title'))

@php
    // Phones: list or map (?view=map); kept in every filter link. Laptops always show the list.
    $view = request('view') === 'map' ? 'map' : 'list';

    // Build a /rooms URL from the current filters with some changed; empty values drop out (and so does ?page).
    $url = fn (array $change) => route('rooms.index', array_filter(
        array_merge(['view' => $view === 'map' ? 'map' : null], $filters, $change),
        fn ($v) => $v !== null && $v !== '' && $v !== false
    ));

    // Phone filter button badge: everything that lives in the filter sheet (not sport or text search)
    $activeFilters = collect(['date', 'time', 'venue', 'free', 'when', 'price', 'city'])->filter(fn ($k) => ! empty($filters[$k]))->count();
    $cityItems = collect([['label' => __('rooms.index.all_cities'), 'href' => $url(['city' => null]), 'active' => empty($filters['city'])]])
        ->concat($cities->map(fn ($c) => ['label' => $c->label(), 'href' => $url(['city' => $c->slug]), 'active' => ($filters['city'] ?? null) === $c->slug]))->all();
    $cityCurrent = $cities->firstWhere('slug', $filters['city'] ?? null)?->label() ?? __('rooms.index.all_cities');

    $today = today()->toDateString();
    $tomorrow = today()->addDay()->toDateString();
    $date = $filters['date'] ?? null;
    $venue = $filters['venue'] ?? null;
    $free = ! empty($filters['free']);
    $weekend = ($filters['when'] ?? null) === 'weekend';
    $gratis = ($filters['price'] ?? null) === 'free';
    $sportSlug = $filters['sport'] ?? null;

    $dayLabel = fn (string $day) => match ($day) {
        $today => __('rooms.index.today'),
        $tomorrow => __('rooms.index.tomorrow'),
        default => \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Carbon::parse($day)->locale(app()->getLocale())->isoFormat('dddd, D MMMM')),
    };
    $groups = $rooms->getCollection()->groupBy(fn ($room) => $room->match_date_time->toDateString());
    $hasFilters = (bool) array_filter($filters);
@endphp

@push('styles')
<style>
    /* /rooms only. Tokens only. */
    .rooms-head { display: flex; align-items: center; justify-content: space-between; gap: var(--space-3); }
    .rooms-head__title { display: grid; }
    .rooms-head__actions { display: flex; align-items: center; gap: var(--space-2); }
    .rooms-head__create { display: none; }

    .search { position: relative; margin-top: var(--space-4); }
    .search .icon { position: absolute; left: var(--space-3); top: 50%; transform: translateY(-50%); width: 20px; height: 20px; color: var(--text-2); pointer-events: none; }
    .search .input { padding-left: calc(var(--space-3) + 20px + var(--space-2)); }
    .search .input::-webkit-search-cancel-button { cursor: pointer; }

    .rooms-filters { display: grid; gap: var(--space-1); margin-top: var(--space-3); }
    .more-filters { margin-top: var(--space-2); }
    .more-filters summary {
        display: inline-flex; align-items: center; gap: var(--space-2); min-height: var(--tap);
        color: var(--brand); font-size: var(--fs-callout); font-weight: var(--fw-medium); cursor: pointer; list-style: none;
    }
    .more-filters summary::-webkit-details-marker { display: none; }
    .more-filters summary .icon { width: 18px; height: 18px; transition: transform var(--duration) var(--ease); }
    .more-filters[open] summary .icon { transform: rotate(90deg); }
    .more-filters__body { display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-3); padding-block: var(--space-2) var(--space-4); }
    .more-filters__actions { grid-column: 1 / -1; display: flex; gap: var(--space-3); align-items: center; }

    .rooms-results { margin-top: var(--space-5); }
    .day-group + .day-group { margin-top: var(--space-6); }
    .day-group > h2 { margin-bottom: var(--space-3); }

    @media (min-width: 1024px) {
        .rooms-head__create { display: inline-flex; }
        .more-filters__body { grid-template-columns: 200px 160px auto; align-items: end; }
        .more-filters__actions { grid-column: auto; }
        /* the sport chips come first in the markup (outside the phone filter sheet); laptops keep the old order */
        .rooms-filters__when { order: 1; }
        .rooms-filters__sports { order: 2; }
        .more-filters { order: 3; }
    }

    /* Phones (Figma Explore): search + filter button, one row of sports, List / Map */
    @media (max-width: 1023px) {
        .rooms-head__actions .dropdown { display: none; }   /* the city moves into the filter sheet */
        .rooms-search { display: flex; gap: var(--space-2); align-items: flex-end; }
        .rooms-search .search { flex: 1; min-width: 0; }
        .rooms-filter-btn {
            position: relative; flex: none; display: grid; place-items: center; width: 48px; height: 48px;
            border: 1px solid var(--separator); border-radius: var(--radius-control); background: var(--bg); color: var(--text);
        }
        .rooms-filter-btn:hover { background: var(--bg-subtle); }
        .rooms-filter-btn .icon { width: 22px; height: 22px; }
        .rooms-filter-btn__count { position: absolute; top: -6px; right: -6px; min-width: 20px; height: 20px; padding: 0 6px; border-radius: var(--radius-pill); display: grid; place-items: center; background: var(--brand); color: var(--on-brand); font-size: var(--fs-caption); font-weight: var(--fw-bold); box-shadow: 0 0 0 2px var(--bg); }
        .rooms-filters { gap: 0; }
        .sheet .chips { flex-wrap: wrap; overflow: visible; margin: 0; padding: var(--space-1) 0; }
        .sheet .more-filters { margin-top: var(--space-3); }
        .rooms-sheet__city { margin-bottom: var(--space-3); }
        .rooms-view { margin-top: var(--space-3); }
        .rooms-view a { gap: var(--space-2); }
        .rooms-view .icon { width: 18px; height: 18px; }
    }
</style>
@endpush

@section('content')
<div x-data="{ loading: false }" x-on:pageshow.window="loading = false">
    <form method="GET" action="{{ route('rooms.index') }}" role="search" x-on:submit="loading = true">
        {{-- Keep the quick filters when searching or changing city --}}
        @foreach (['sport', 'date', 'time', 'venue', 'free', 'when', 'price'] as $keep)
            @if (! empty($filters[$keep]))
                <input type="hidden" name="{{ $keep }}" value="{{ $filters[$keep] }}">
            @endif
        @endforeach
        @if ($view === 'map')<input type="hidden" name="view" value="map">@endif

        <header class="rooms-head">
            <div class="rooms-head__title">
                <h1 class="t-large">{{ __('rooms.index.title') }}</h1>
                <p class="t-callout t-secondary" aria-live="polite">{{ trans_choice('rooms.index.count', $rooms->total(), ['count' => $rooms->total()]) }}</p>
            </div>
            <div class="rooms-head__actions">
                <x-dropdown :label="__('rooms.index.city')" :current="$cityCurrent" :items="$cityItems" />
                <x-button href="{{ route('rooms.create', array_filter(['sport' => $sportSlug])) }}" icon="circle-plus" class="rooms-head__create">
                    {{ __('rooms.index.create') }}
                </x-button>
            </div>
        </header>

        <div class="rooms-search">
            <div class="search">
                <label class="sr-only" for="q">{{ __('rooms.index.search') }}</label>
                <x-icon.search />
                <input id="q" type="search" name="q" class="input" value="{{ $filters['q'] ?? '' }}" maxlength="80"
                       placeholder="{{ __('rooms.index.search_placeholder') }}" enterkeyhint="search" autocomplete="off">
            </div>
            {{-- Phones: every filter except the sport lives in a sheet --}}
            <button type="button" class="rooms-filter-btn m-only" x-on:click="$dispatch('open-sheet', 'rooms-filters')"
                    aria-label="{{ __('rooms.index.filters_button') }}@if ($activeFilters) ({{ $activeFilters }})@endif">
                <x-icon.sliders-horizontal />
                @if ($activeFilters)<span class="rooms-filter-btn__count" aria-hidden="true">{{ $activeFilters }}</span>@endif
            </button>
        </div>
    </form>

    <div class="rooms-filters">
        <nav class="chips rooms-filters__sports" aria-label="{{ __('rooms.index.sports') }}">
            <x-chip :href="$url(['sport' => null])" :active="! $sportSlug" x-on:click="loading = true">{{ __('rooms.index.all_sports') }}</x-chip>
            @foreach ($sports as $sport)
                <x-chip :href="$url(['sport' => $sportSlug === $sport->slug ? null : $sport->slug])" :sport="$sport"
                        :active="$sportSlug === $sport->slug" x-on:click="loading = true">
                    {{ $sport->label() }}
                </x-chip>
            @endforeach
        </nav>

        <x-sheet name="rooms-filters" :title="__('rooms.index.filters_title')" inline-desktop>
        <div class="rooms-sheet__city m-only">
            <x-dropdown align="start" :label="__('rooms.index.city')" :current="$cityCurrent" :items="$cityItems" />
        </div>

        <nav class="chips rooms-filters__when" aria-label="{{ __('rooms.index.filters') }}">
            <x-chip :href="$url(['date' => null, 'venue' => null, 'free' => null, 'when' => null, 'price' => null])" :active="! $date && ! $venue && ! $free && ! $weekend && ! $gratis" x-on:click="loading = true">
                {{ __('rooms.index.all') }}
            </x-chip>
            <x-chip :href="$url(['date' => $date === $today ? null : $today, 'when' => null])" :active="$date === $today" x-on:click="loading = true">
                {{ __('rooms.index.today') }}
            </x-chip>
            <x-chip :href="$url(['date' => $date === $tomorrow ? null : $tomorrow, 'when' => null])" :active="$date === $tomorrow" x-on:click="loading = true">
                {{ __('rooms.index.tomorrow') }}
            </x-chip>
            <x-chip :href="$url(['when' => $weekend ? null : 'weekend', 'date' => null])" :active="$weekend" x-on:click="loading = true">
                {{ __('rooms.index.weekend') }}
            </x-chip>
            <x-chip :href="$url(['venue' => $venue === 'indoor' ? null : 'indoor'])" :active="$venue === 'indoor'" x-on:click="loading = true">
                {{ __('rooms.index.indoor') }}
            </x-chip>
            <x-chip :href="$url(['venue' => $venue === 'outdoor' ? null : 'outdoor'])" :active="$venue === 'outdoor'" x-on:click="loading = true">
                {{ __('rooms.index.outdoor') }}
            </x-chip>
            <x-chip :href="$url(['free' => $free ? null : 1])" :active="$free" x-on:click="loading = true">
                {{ __('rooms.index.free_spots') }}
            </x-chip>
            <x-chip :href="$url(['price' => $gratis ? null : 'free'])" :active="$gratis" x-on:click="loading = true">
                {{ __('rooms.index.gratis') }}
            </x-chip>
        </nav>

        {{-- Less common filters stay one tap away, out of the main row --}}
        <details class="more-filters" @if (! empty($filters['time']) || ($date && ! in_array($date, [$today, $tomorrow], true))) open @endif>
            <summary><x-icon.chevron-right /> {{ __('rooms.index.more_filters') }}</summary>
            <form method="GET" action="{{ route('rooms.index') }}" class="more-filters__body" x-on:submit="loading = true">
                @foreach (['city', 'sport', 'venue', 'free', 'q', 'price'] as $keep)
                    @if (! empty($filters[$keep]))
                        <input type="hidden" name="{{ $keep }}" value="{{ $filters[$keep] }}">
                    @endif
                @endforeach
                @if ($view === 'map')<input type="hidden" name="view" value="map">@endif
                <x-field name="date" type="date" :label="__('rooms.index.date')" :value="$date" :min="$today" />
                <x-field name="time" type="time" :label="__('rooms.index.from_time')" :value="$filters['time'] ?? null" step="1800" />
                <div class="more-filters__actions">
                    <x-button variant="secondary" type="submit">{{ __('rooms.index.apply') }}</x-button>
                    @if ($hasFilters)
                        <a href="{{ route('rooms.index') }}" x-on:click="loading = true">{{ __('rooms.index.reset') }}</a>
                    @endif
                </div>
            </form>
        </details>
        </x-sheet>
    </div>

    {{-- Phones: list or map --}}
    <nav class="segmented rooms-view m-only" aria-label="{{ __('rooms.index.view_label') }}">
        <a href="{{ $url(['view' => null]) }}" @if ($view === 'list') aria-current="page" @endif><x-icon.list /> <span>{{ __('rooms.index.view_list') }}</span></a>
        <a href="{{ $url(['view' => 'map']) }}" @if ($view === 'map') aria-current="page" @endif><x-icon.map /> <span>{{ __('rooms.index.view_map') }}</span></a>
    </nav>

    @if ($view === 'map')
        @php
            $mapConfig = [
                'center' => $cities->firstWhere('slug', $filters['city'] ?? null)?->center() ?? \App\Models\City::CENTERS['chisinau'],
                'icons' => $sports->mapWithKeys(fn ($s) => [$s->slug => \Illuminate\Support\Facades\Blade::render('<x-dynamic-component :component="$c" class="icon" />', ['c' => $s->icon()])]),
                't' => [
                    'price' => __('match.price.amount'),
                    'free' => __('match.price.free'),
                    'spots' => collect(range(1, 50))->mapWithKeys(fn ($n) => [$n => trans_choice('components.match.spots_left', $n, ['count' => $n])]),
                ],
            ];
        @endphp
        @push('styles')
            <link rel="stylesheet" href="{{ \App\Support\Asset::url('assets/css/map.css') }}">
        @endpush
        @push('scripts')
            <script src="{{ \App\Support\Asset::url('assets/js/rooms-map.js') }}"></script>
        @endpush
        {{-- Every open match that still needs players, from the whole country; the city filter does not apply here --}}
        <section id="rooms-map" class="rooms-map m-only" aria-label="{{ __('rooms.index.map_label') }}"
                 x-data="roomsMap(@js($mapConfig))" data-url="{{ route('rooms.map', array_filter(array_diff_key($filters, ['city' => true]), fn ($v) => $v !== null && $v !== '')) }}">
            <div class="rooms-map__canvas" x-ref="canvas"></div>
            <x-skeleton class="rooms-map__loading" x-show="state === 'loading'" />

            <div class="rooms-map__msg" x-show="state === 'empty'" x-cloak role="status">
                <p class="t-headline">{{ __('rooms.index.map_empty') }}</p>
                <p class="t-footnote t-secondary">{{ __('rooms.index.map_empty_hint') }}</p>
            </div>
            <div class="rooms-map__msg" x-show="state === 'error'" x-cloak role="alert">
                <p class="t-headline">{{ __('rooms.index.map_error') }}</p>
                <x-button variant="secondary" size="sm" x-on:click="load()">{{ __('rooms.index.map_retry') }}</x-button>
            </div>

            <button type="button" class="icon-btn rooms-map__locate" x-on:click="locate()" :disabled="locating"
                    aria-label="{{ __('rooms.index.map_near') }}" title="{{ __('rooms.index.map_near') }}"><x-icon.locate /></button>
            <p class="rooms-map__toast t-footnote" x-show="geoError" x-cloak role="status">{{ __('rooms.index.map_geo_denied') }}</p>
            <p class="rooms-map__toast t-footnote" x-show="truncated && !peek && !geoError" x-cloak>{{ __('rooms.index.map_truncated') }}</p>

            {{-- The tapped pin's match, as the same compact row as the list --}}
            <div class="rooms-map__peek" x-ref="peek" x-show="peek" x-cloak>
                <template x-if="peek">
                    <a class="match-row" :href="peek.url" :style="`--c: var(${peek.sport.var})`">
                        <span class="match-row__square" aria-hidden="true">
                            <span class="match-row__day" x-text="peek.day.split(',')[0].replace('.', '').toUpperCase()"></span>
                            <span class="match-row__big tabular" x-text="peek.time"></span>
                        </span>
                        <span class="match-row__body">
                            <span class="match-row__title" x-text="peek.title"></span>
                            <span class="match-row__meta">
                                <span class="sport-icon sport-icon--xs" :style="`--c: var(${peek.sport.var}); --on: var(--on-${peek.sport.var.slice(2)})`" x-html="icon(peek)" aria-hidden="true"></span>
                                <span x-text="peek.sport.name"></span>
                                <span aria-hidden="true">·</span>
                                <span class="match-row__place" data-distance :data-lat="peek.lat" :data-lng="peek.lng" x-text="peek.city"></span>
                            </span>
                            <span class="match-row__tags">
                                <span class="match-row__price" x-text="price(peek)"></span>
                                <span class="badge" :class="'badge--' + peek.availability"><span class="badge__dot" aria-hidden="true"></span><span x-text="spots(peek)"></span></span>
                            </span>
                        </span>
                        <span class="match-row__end"><x-icon.chevron-right class="icon match-row__chevron" /></span>
                    </a>
                </template>
            </div>
        </section>
    @endif

    <div @class(['rooms-results', 'd-only' => $view === 'map'])>
        {{-- Shown while the next page of results loads (filters reload the page) --}}
        <x-skeleton variant="match-card" :count="3" class="match-list" x-show="loading" x-cloak />

        <div x-show="!loading">
            @forelse ($groups as $day => $dayRooms)
                <section class="day-group" aria-labelledby="day-{{ $day }}">
                    <h2 id="day-{{ $day }}" class="t-title2">{{ $dayLabel($day) }}</h2>
                    <div class="match-list d-only">
                        @foreach ($dayRooms as $room)
                            <x-match-card :room="$room" />
                        @endforeach
                    </div>
                    <div class="match-rows m-only">
                        @foreach ($dayRooms as $room)
                            <x-match-row :room="$room" />
                        @endforeach
                    </div>
                </section>
            @empty
                <x-empty-state icon="calendar-x" :title="__('rooms.index.empty_title')" :text="__('rooms.index.empty_text')">
                    <x-button href="{{ route('rooms.create', array_filter(['sport' => $sportSlug])) }}" icon="circle-plus">
                        {{ __('rooms.index.empty_action') }}
                    </x-button>
                    @if ($hasFilters)
                        <p style="margin-top: var(--space-3)"><a href="{{ route('rooms.index') }}">{{ __('rooms.index.reset') }}</a></p>
                    @endif
                </x-empty-state>
            @endforelse

            @if ($rooms->hasPages())
                <nav class="pager-row" aria-label="{{ __('rooms.index.page', ['current' => $rooms->currentPage(), 'last' => $rooms->lastPage()]) }}">
                    <span class="t-callout t-secondary">{{ __('rooms.index.page', ['current' => $rooms->currentPage(), 'last' => $rooms->lastPage()]) }}</span>
                    <span class="rooms-head__actions">
                        @if ($rooms->previousPageUrl())
                            <x-button variant="secondary" :href="$rooms->previousPageUrl()" icon="arrow-left" x-on:click="loading = true">{{ __('rooms.index.previous') }}</x-button>
                        @endif
                        @if ($rooms->nextPageUrl())
                            <x-button :href="$rooms->nextPageUrl()" icon-right="arrow-right" x-on:click="loading = true">{{ __('rooms.index.next') }}</x-button>
                        @endif
                    </span>
                </nav>
            @endif
        </div>
    </div>
</div>
@endsection
