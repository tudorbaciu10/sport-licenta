@extends('layouts.app')

@section('title', __('rooms.index.title'))

@php
    // Build a /rooms URL from the current filters with some changed; empty values drop out (and so does ?page).
    $url = fn (array $change) => route('rooms.index', array_filter(
        array_merge($filters, $change),
        fn ($v) => $v !== null && $v !== '' && $v !== false
    ));

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
    .city-select { min-width: 0; }
    .city-select .input {
        min-height: var(--tap); padding: 0 var(--space-7) 0 var(--space-4); border-radius: var(--radius-pill);
        font-size: var(--fs-callout); font-weight: var(--fw-semibold); max-width: 190px; text-overflow: ellipsis;
    }

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

        <header class="rooms-head">
            <div class="rooms-head__title">
                <h1 class="t-large">{{ __('rooms.index.title') }}</h1>
                <p class="t-callout t-secondary" aria-live="polite">{{ trans_choice('rooms.index.count', $rooms->total(), ['count' => $rooms->total()]) }}</p>
            </div>
            <div class="rooms-head__actions">
                <label class="sr-only" for="city">{{ __('rooms.index.city') }}</label>
                <div class="select city-select">
                    <select id="city" name="city" class="input" x-on:change="loading = true; $el.form.submit()">
                        <option value="">{{ __('rooms.index.all_cities') }}</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->slug }}" @selected(($filters['city'] ?? null) === $city->slug)>{{ $city->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <x-button href="{{ route('rooms.create', array_filter(['sport' => $sportSlug])) }}" icon="circle-plus" class="rooms-head__create">
                    {{ __('rooms.index.create') }}
                </x-button>
            </div>
        </header>

        <div class="search">
            <label class="sr-only" for="q">{{ __('rooms.index.search') }}</label>
            <x-icon.search />
            <input id="q" type="search" name="q" class="input" value="{{ $filters['q'] ?? '' }}" maxlength="80"
                   placeholder="{{ __('rooms.index.search_placeholder') }}" enterkeyhint="search" autocomplete="off">
        </div>
    </form>

    <div class="rooms-filters">
        <nav class="chips" aria-label="{{ __('rooms.index.filters') }}">
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

        <nav class="chips" aria-label="{{ __('rooms.index.sports') }}">
            <x-chip :href="$url(['sport' => null])" :active="! $sportSlug" x-on:click="loading = true">{{ __('rooms.index.all_sports') }}</x-chip>
            @foreach ($sports as $sport)
                <x-chip :href="$url(['sport' => $sportSlug === $sport->slug ? null : $sport->slug])" :sport="$sport"
                        :active="$sportSlug === $sport->slug" x-on:click="loading = true">
                    {{ $sport->label() }}
                </x-chip>
            @endforeach
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
    </div>

    <div class="rooms-results">
        {{-- Shown while the next page of results loads (filters reload the page) --}}
        <x-skeleton variant="match-card" :count="3" class="match-list" x-show="loading" x-cloak />

        <div x-show="!loading">
            @forelse ($groups as $day => $dayRooms)
                <section class="day-group" aria-labelledby="day-{{ $day }}">
                    <h2 id="day-{{ $day }}" class="t-title2">{{ $dayLabel($day) }}</h2>
                    <div class="match-list">
                        @foreach ($dayRooms as $room)
                            <x-match-card :room="$room" />
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
