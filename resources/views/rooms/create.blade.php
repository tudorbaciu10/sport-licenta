@extends('layouts.app')

@section('title', __('match.create.title'))
@section('no_tabbar', true)

@php
    // ?date=YYYY-MM-DD from the calendar pre-fills the day (18:00, change as needed)
    $prefill = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) request('date')) && request('date') >= today()->toDateString() ? request('date').'T18:00' : '';
    $init = [
        'sport' => (string) old('sport_id', $selectedSport),
        'title' => old('title', ''),
        'when' => (string) old('match_date_time', $prefill),
        'location' => old('location_name', ''),
        'lat' => old('latitude', ''),
        'lng' => old('longitude', ''),
        'city' => (string) old('city_id', auth()->user()->city_id ?? ''),
        'venue' => old('venue_type', ''),
        'players' => (int) old('max_players', 10),
        'price' => (int) old('price', 0),
        'collector' => old('price_collector', ''),
        'rules' => array_values(old('rules', [''])) ?: [''],
        'locale' => app()->getLocale(),
        'today' => today()->toDateString(),
        'now' => now()->format('H:i'),
        'maxDate' => today()->addMonths(6)->subDay()->toDateString(),
        'daysUrl' => route('calendar.days'),
        'dayUrl' => route('calendar.day'),
        'defaultCenter' => \App\Models\City::CENTERS['chisinau'],
        'sports' => $sports->mapWithKeys(fn ($s) => [$s->id => ['name' => $s->label(), 'var' => $s->cssVar(), 'slug' => $s->slug]]),
        'cities' => $cities->mapWithKeys(fn ($c) => [$c->id => ['name' => $c->label(), 'slug' => $c->slug, 'center' => $c->center()]]),
        't' => [
            'free' => __('match.price.free'),
            'lei' => __('match.price.amount', ['price' => '__P__']),
            'whenRequired' => __('validation.custom.match_date_time.required'),
            'whenPast' => __('validation.custom.match_date_time.after'),
            'dayCount' => trans_choice('match.create.day_count', 5, ['count' => ':count']),
            'mapPin' => __('match.create.map_set'),
        ],
    ];
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ \App\Support\Asset::url('assets/css/create.css') }}">
@endpush

@section('content')
<div class="create" x-data="createMatch(@js($init))" x-on:step-changed="current = $event.detail">
    {{-- Phones (Figma App Bar): back + title; the steps bar follows in the form --}}
    <header class="create-appbar m-only">
        <a class="icon-btn" href="{{ route('rooms.index') }}" aria-label="{{ __('match.show.back') }}"><x-icon.arrow-left /></a>
        <h1 class="t-headline">{{ __('match.create.title') }}</h1>
        <span class="create-appbar__spacer" aria-hidden="true"></span>
    </header>
    <header class="create-head d-only">
        <h1 class="t-large">{{ __('match.create.title') }}</h1>
        <p class="t-callout t-secondary">{{ __('match.create.intro') }}</p>
    </header>

    <div class="create-layout">
        {{-- Left: the form, one small group of fields per step --}}
        <div class="create-main">
            <x-step-form :steps="__('match.create.steps')" :action="route('rooms.store')" :submit="__('match.create.submit')">
                {{-- 1. Sport și oraș --}}
                <x-step-form.step :n="1">
                    <div @class(['field', 'field--error' => $errors->has('sport_id')])>
                        <p class="group-label" id="sport-label">{{ __('match.create.sport') }}</p>
                        <div class="sport-pick" role="radiogroup" aria-labelledby="sport-label">
                            @foreach ($sports as $sport)
                                <label style="--c: var({{ $sport->cssVar() }})">
                                    <input type="radio" name="sport_id" value="{{ $sport->id }}" x-model="sport" required>
                                    <x-sport-icon :sport="$sport" size="md" />
                                    <span class="t-headline">{{ $sport->label() }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('sport_id')<p class="field__error"><x-icon.circle-alert /> <span>{{ $message }}</span></p>@enderror
                    </div>

                    <x-field name="city_id" type="select" :label="__('match.create.city')" :placeholder="__('match.create.choose')" required
                             :options="$cities->mapWithKeys(fn ($c) => [$c->id => $c->label()])->all()" x-model="city" />

                    <x-field name="title" :label="__('match.create.match_title')" :placeholder="__('match.create.match_title_ph')"
                             maxlength="120" required x-model="title" />
                </x-step-form.step>

                {{-- 2. Când și unde --}}
                <x-step-form.step :n="2">
                    <div @class(['field', 'when-field', 'field--error' => $errors->has('match_date_time')])>
                        <p class="group-label" id="when-label">{{ __('match.create.when') }}</p>
                        <input type="hidden" name="match_date_time" :value="when" value="{{ $init['when'] }}">
                        {{-- Laptops: the calendar is in the right column; here only what was chosen --}}
                        <div class="when-summary" aria-live="polite">
                            <x-icon.calendar-days />
                            <span>
                                <strong x-show="date" x-text="whenLabel"></strong>
                                <span class="t-secondary" x-show="!date">{{ __('match.create.when_none') }}</span>
                                <span class="t-footnote t-secondary when-summary__hint">{{ __('match.create.when_hint_side') }}</span>
                            </span>
                        </div>
                        {{-- Phones and tablets (Figma Select Date & Time): the week, then the times; the month opens in a sheet --}}
                        <div class="when-inline when-phone">
                            <div class="when-phone__head">
                                <span class="t-headline">{{ __('match.create.day') }}</span>
                                <button type="button" class="icon-btn" x-on:click="$dispatch('open-sheet', 'when-month')" aria-label="{{ __('match.create.month') }}"><x-icon.calendar-days /></button>
                            </div>
                            <x-week-strip mode="count" :city="$cities->firstWhere('id', (int) $init['city'])?->slug" :selected="$init['when'] ? substr($init['when'], 0, 10) : null"
                                          x-on:week-day="pickDate($event.detail.date)" />
                            @include('rooms.partials.when-slots', ['id' => 'inline'])
                            <x-sheet name="when-month" :title="__('match.create.month')">
                                <div x-on:date-picked.window="$dispatch('close-sheet')">
                                    @include('rooms.partials.when-calendar', ['id' => 'sheet'])
                                </div>
                            </x-sheet>
                        </div>
                        @error('match_date_time')<p class="field__error"><x-icon.circle-alert /> <span>{{ $message }}</span></p>@enderror
                    </div>

                    <div class="when-inline">
                        @include('rooms.partials.day-matches', ['id' => 'inline'])
                    </div>

                    <div @class(['field', 'field--error' => $errors->has('location_name') || $errors->has('latitude')])>
                        <label class="field__label" for="f-location_name">{{ __('match.create.where') }}</label>
                        <input id="f-location_name" class="input" name="location_name" maxlength="160" required x-model="location"
                               placeholder="{{ __('match.create.where_ph') }}" autocomplete="off" @error('location_name') aria-invalid="true" @enderror>
                        <input type="hidden" name="latitude" :value="lat" value="{{ $init['lat'] }}">
                        <input type="hidden" name="longitude" :value="lng" value="{{ $init['lng'] }}">

                        <div class="loc-actions">
                            <button type="button" class="btn btn--secondary btn--sm" x-on:click="toggleMap()" :aria-expanded="mapOpen" aria-controls="map-box">
                                <x-icon.map-pin class="icon icon--sm" />
                                <span x-text="mapOpen ? @js(__('match.create.map_close')) : @js(__('match.create.map_open'))">{{ __('match.create.map_open') }}</span>
                            </button>
                            <span class="loc-pin t-footnote" x-show="hasPin" x-cloak>
                                <x-icon.circle-check /> {{ __('match.create.map_set') }}
                                <button type="button" class="link-btn" x-on:click="clearPin()">{{ __('match.create.map_clear') }}</button>
                            </span>
                        </div>

                        <div id="map-box" class="map-box" x-show="mapOpen" x-cloak>
                            <div class="map-search" role="search">
                                <label for="map-q" class="sr-only">{{ __('match.create.map_search') }}</label>
                                <input id="map-q" type="search" class="input" x-model="mapQuery" placeholder="{{ __('match.create.map_search') }}"
                                       enterkeyhint="search" autocomplete="off" x-on:keydown.enter.prevent="search()">
                                <button type="button" class="btn btn--primary btn--sm" x-on:click="search()" :disabled="mapSearching">{{ __('match.create.map_search_btn') }}</button>
                            </div>
                            <ul class="map-results" role="list" x-show="mapResults.length" aria-live="polite">
                                <template x-for="r in mapResults" :key="r.id">
                                    <li><button type="button" class="map-result" x-on:click="pickResult(r)">
                                        <x-icon.map-pin class="icon icon--sm" />
                                        <span><strong x-text="r.name"></strong><span class="t-footnote t-secondary" x-text="r.address"></span></span>
                                    </button></li>
                                </template>
                            </ul>
                            <p class="t-footnote t-secondary" x-show="mapNone" role="status">{{ __('match.create.map_none') }}</p>
                            <div class="map" x-ref="map" role="application" aria-label="{{ __('match.create.map_label') }}">
                                <x-skeleton x-show="mapLoading" class="map__loading" />
                            </div>
                            <p class="field__error" x-show="mapError" role="alert"><x-icon.circle-alert /> <span>{{ __('match.create.map_error') }}</span></p>
                            <div class="map-foot">
                                <button type="button" class="btn btn--ghost btn--sm" x-on:click="locate()"><x-icon.map-pin class="icon icon--sm" /> {{ __('match.create.map_me') }}</button>
                                <span class="t-footnote t-secondary">{{ __('match.create.map_hint') }}</span>
                            </div>
                            <p class="t-footnote" x-show="geoError" role="status">{{ __('match.create.geo_error') }}</p>
                        </div>

                        @error('location_name')<p class="field__error"><x-icon.circle-alert /> <span>{{ $message }}</span></p>@enderror
                        @error('latitude')<p class="field__error"><x-icon.circle-alert /> <span>{{ $message }}</span></p>@enderror
                    </div>

                    <div class="field">
                        <p class="group-label" id="venue-label">{{ __('match.create.venue') }} <span class="t-secondary">({{ __('components.field.optional') }})</span></p>
                        <div class="choices" role="radiogroup" aria-labelledby="venue-label">
                            @foreach (['indoor' => __('rooms.index.indoor'), 'outdoor' => __('rooms.index.outdoor'), '' => __('match.create.venue_any')] as $value => $label)
                                <label><input type="radio" name="venue_type" value="{{ $value }}" x-model="venue">{{ $label }}</label>
                            @endforeach
                        </div>
                    </div>
                </x-step-form.step>

                {{-- 3. Jucători și preț --}}
                <x-step-form.step :n="3">
                    <x-field name="max_players" type="number" :label="__('match.create.players')" min="2" max="50" required
                             inputmode="numeric" x-model.number="players" />
                    <x-field name="price" type="number" :label="__('match.create.price')" :hint="__('match.create.price_hint')"
                             min="0" max="1000" inputmode="numeric" x-model.number="price" optional />

                    <div @class(['field', 'field--error' => $errors->has('price_collector')]) x-show="price > 0">
                        <p class="group-label" id="collector-label">{{ __('match.create.collector') }}</p>
                        <div class="choices" role="radiogroup" aria-labelledby="collector-label">
                            @foreach (\App\Models\Room::PRICE_COLLECTORS as $value)
                                <label><input type="radio" name="price_collector" value="{{ $value }}" x-model="collector" :required="price > 0">{{ __('match.price.collector.'.$value) }}</label>
                            @endforeach
                        </div>
                        @error('price_collector')<p class="field__error"><x-icon.circle-alert /> <span>{{ $message }}</span></p>@enderror
                    </div>

                    <div class="field">
                        <p class="group-label" id="equipment-label">{{ __('match.create.equipment') }} <span class="t-secondary">({{ __('components.field.optional') }})</span></p>
                        <div class="choices" role="radiogroup" aria-labelledby="equipment-label">
                            @foreach (\App\Models\Room::EQUIPMENT_BY as $value)
                                <label><input type="radio" name="equipment_by" value="{{ $value }}" @checked(old('equipment_by') === $value)>{{ __('match.equipment.'.$value) }}</label>
                            @endforeach
                            <label><input type="radio" name="equipment_by" value="" @checked(! old('equipment_by'))>{{ __('match.create.equipment_none') }}</label>
                        </div>
                    </div>
                </x-step-form.step>

                {{-- 4. Reguli și notă --}}
                <x-step-form.step :n="4">
                    <x-field name="description" type="textarea" :label="__('match.create.note')" :placeholder="__('match.create.note_ph')"
                             maxlength="2000" optional />

                    <div @class(['field', 'field--error' => $errors->has('rules.*')])>
                        <p class="group-label">{{ __('match.create.rules') }} <span class="t-secondary">({{ __('components.field.optional') }})</span></p>
                        <template x-for="(rule, i) in rules" :key="i">
                            <div class="rule-row">
                                <input class="input" name="rules[]" x-model="rules[i]" maxlength="160"
                                       :aria-label="@js(__('match.create.rules')) + ' ' + (i + 1)" placeholder="{{ __('match.create.rule_ph') }}">
                                <button type="button" class="btn btn--ghost" x-show="rules.length > 1" x-on:click="rules.splice(i, 1)"
                                        aria-label="{{ __('match.create.remove_rule') }}"><x-icon.x class="icon icon--sm" /></button>
                            </div>
                        </template>
                        <noscript><input class="input" name="rules[]" maxlength="160" placeholder="{{ __('match.create.rule_ph') }}"></noscript>
                        <div><x-button variant="ghost" icon="circle-plus" x-show="rules.length < 10" x-on:click="rules.push('')">{{ __('match.create.add_rule') }}</x-button></div>
                        @error('rules.*')<p class="field__error"><x-icon.circle-alert /> <span>{{ $message }}</span></p>@enderror
                    </div>

                    <div class="when-inline">
                        @include('rooms.partials.preview', ['id' => 'inline'])
                    </div>
                </x-step-form.step>

                {{-- Phones: what was chosen, next to "Continuă" (step 2) --}}
                <x-slot:navSummary>
                    <span x-show="current === 2" class="step-form__when">
                        <strong x-show="date" x-text="whenLabel"></strong>
                        <span class="t-footnote t-secondary" x-show="!date">{{ __('match.create.when_none') }}</span>
                    </span>
                </x-slot:navSummary>
            </x-step-form>
        </div>

        {{-- Right (laptops): plan with the calendar, see the day, watch the card take shape --}}
        <aside class="create-side" aria-label="{{ __('match.create.calendar') }}">
            <section class="side-card">
                <h2 class="sr-only">{{ __('match.create.calendar') }}</h2>
                @include('rooms.partials.when-picker', ['id' => 'side'])
            </section>
            <section class="side-card">
                @include('rooms.partials.day-matches', ['id' => 'side'])
            </section>
            <section class="side-card">
                @include('rooms.partials.preview', ['id' => 'side'])
                <ol class="side-steps" role="list" aria-label="{{ __('match.create.progress') }}">
                    @foreach (__('match.create.steps') as $i => $label)
                        <li>
                            <button type="button" class="side-step" x-on:click="goTo({{ $i + 1 }})"
                                    :class="{ 'is-done': done[{{ $i }}], 'is-current': current === {{ $i + 1 }} }"
                                    :aria-current="current === {{ $i + 1 }} ? 'step' : null">
                                <span class="side-step__mark" aria-hidden="true">
                                    <span x-show="done[{{ $i }}]"><x-icon.check /></span>
                                    <span x-show="!done[{{ $i }}]">{{ $i + 1 }}</span>
                                </span>
                                <span>{{ $label }}</span>
                            </button>
                        </li>
                    @endforeach
                </ol>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ \App\Support\Asset::url('assets/js/create-match.js') }}"></script>
@endpush
