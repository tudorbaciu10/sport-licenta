@extends('layouts.app')

@section('title', __('match.create.title'))

@php
    $init = [
        'sport' => (string) old('sport_id', $selectedSport),
        'title' => old('title', ''),
        'when' => old('match_date_time', ''),
        'location' => old('location_name', ''),
        'city' => (string) old('city_id', ''),
        'venue' => old('venue_type', ''),
        'players' => (int) old('max_players', 10),
        'price' => (int) old('price', 0),
        'collector' => old('price_collector', ''),
        'rules' => array_values(old('rules', [''])) ?: [''],
        'locale' => app()->getLocale(),
        'sports' => $sports->mapWithKeys(fn ($s) => [$s->id => ['name' => $s->label(), 'var' => $s->cssVar()]]),
        'cities' => $cities->mapWithKeys(fn ($c) => [$c->id => $c->label()]),
    ];
@endphp

@push('styles')
<style>
    /* /rooms/create only. Tokens only. */
    .create-head { display: grid; gap: var(--space-1); margin-bottom: var(--space-5); }
    .create-wrap { max-width: 640px; }

    .sport-pick { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: var(--space-2); }
    .sport-pick label {
        display: flex; align-items: center; gap: var(--space-3); min-height: 60px; padding: var(--space-2) var(--space-3);
        border: 1px solid var(--separator); border-radius: var(--radius-control); cursor: pointer;
        transition: border-color var(--duration) var(--ease), background-color var(--duration) var(--ease);
    }
    .sport-pick label:hover { background: var(--bg-subtle); }
    .sport-pick label:has(input:checked) { border-color: var(--c); box-shadow: inset 0 0 0 1px var(--c); background: color-mix(in srgb, var(--c) var(--tint), var(--bg)); }
    .sport-pick label:has(input:focus-visible), .choices label:has(input:focus-visible) { outline: 3px solid var(--brand); outline-offset: 2px; }
    .sport-pick input, .choices input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .group-label { font-size: var(--fs-callout); line-height: var(--lh-callout); font-weight: var(--fw-medium); margin-bottom: var(--space-2); }

    .choices { display: flex; flex-wrap: wrap; gap: var(--space-2); }
    .choices label {
        display: inline-flex; align-items: center; min-height: var(--tap); padding: 0 var(--space-4);
        border: 1px solid var(--separator); border-radius: var(--radius-pill); cursor: pointer;
        font-size: var(--fs-callout); font-weight: var(--fw-medium);
        transition: background-color var(--duration) var(--ease), color var(--duration) var(--ease);
    }
    .choices label:hover { background: var(--bg-subtle); }
    .choices label:has(input:checked) { background: var(--text); border-color: var(--text); color: var(--bg); }

    .rule-row { display: flex; gap: var(--space-2); }
    .rule-row .btn { flex: none; }

    .preview { display: grid; gap: var(--space-3); padding: var(--space-4); border-radius: var(--radius-card); background: var(--bg-subtle); }
    .preview .match-card { pointer-events: none; }

    @media (min-width: 768px) {
        .sport-pick { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
</style>
@endpush

@section('content')
<div class="create-wrap" x-data="createMatch(@js($init))">
    <header class="create-head">
        <h1 class="t-large">{{ __('match.create.title') }}</h1>
        <p class="t-callout t-secondary">{{ __('match.create.intro') }}</p>
    </header>

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
            <x-field name="match_date_time" type="datetime-local" :label="__('match.create.when')" required
                     :min="now()->format('Y-m-d\TH:i')" x-model="when" />
            <x-field name="location_name" :label="__('match.create.where')" :placeholder="__('match.create.where_ph')"
                     maxlength="160" required x-model="location" />
            <div class="field">
                <p class="group-label" id="venue-label">{{ __('match.create.venue') }}</p>
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
                     min="0" max="1000" step="5" inputmode="numeric" x-model.number="price" />

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
                <p class="group-label" id="equipment-label">{{ __('match.create.equipment') }}</p>
                <div class="choices" role="radiogroup" aria-labelledby="equipment-label">
                    @foreach (\App\Models\Room::EQUIPMENT_BY as $value)
                        <label><input type="radio" name="equipment_by" value="{{ $value }}" @checked(old('equipment_by') === $value)>{{ __('match.equipment.'.$value) }}</label>
                    @endforeach
                    <label><input type="radio" name="equipment_by" value="" @checked(! old('equipment_by'))>{{ __('match.create.equipment_none') }}</label>
                </div>
            </div>
        </x-step-form.step>

        {{-- 4. Reguli și notă + previzualizare --}}
        <x-step-form.step :n="4">
            <x-field name="description" type="textarea" :label="__('match.create.note')" :placeholder="__('match.create.note_ph')"
                     maxlength="2000" optional />

            <div class="field">
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

            {{-- Live preview of the list card --}}
            <section class="preview" aria-labelledby="preview-title" x-show="sport" x-cloak>
                <h2 id="preview-title" class="t-headline">{{ __('match.create.preview') }}</h2>
                <div class="match-card" :style="`--c: var(${sports[sport]?.var})`" aria-hidden="true">
                    <div class="match-card__head">
                        @foreach ($sports as $sport)
                            <template x-if="sport == '{{ $sport->id }}'"><x-sport-icon :sport="$sport" size="md" /></template>
                        @endforeach
                        <div class="match-card__when">
                            <span class="t-title2 tabular" x-text="time() || '--:--'"></span>
                            <span class="t-footnote t-secondary" x-text="subline()"></span>
                        </div>
                        <x-badge status="open" class="match-card__badge" />
                    </div>
                    <div class="match-card__body">
                        <h3 class="t-headline match-card__title" x-text="title || @js(__('match.create.match_title_ph'))"></h3>
                        <p class="t-callout t-secondary match-card__meta">
                            <x-icon.map-pin class="icon icon--xs" />
                            <span x-text="[location, cities[city]].filter(Boolean).join(', ') || @js(__('match.create.where_ph'))"></span>
                        </p>
                    </div>
                    <div class="match-card__spots">
                        <div class="progress"><span :style="`width: ${Math.round(100 / Math.max(players, 1))}%`"></span></div>
                        <span class="t-footnote tabular match-card__count"><x-icon.users class="icon icon--xs" /> <span x-text="`1 / ${players || 0}`"></span></span>
                    </div>
                </div>
            </section>
        </x-step-form.step>
    </x-step-form>
</div>
@endsection

@push('scripts')
<script>
    function createMatch(init) {
        return {
            ...init,
            free: @js(__('match.price.free')),
            lei: @js(__('match.price.amount', ['price' => '__P__'])),
            time() { return this.when ? this.when.slice(11, 16) : ''; },
            subline() {
                const parts = [];
                if (this.when) {
                    parts.push(new Date(this.when).toLocaleDateString(this.locale, { weekday: 'short', day: 'numeric', month: 'short' }));
                }
                if (this.sports[this.sport]) parts.push(this.sports[this.sport].name);
                parts.push(this.price > 0 ? this.lei.replace('__P__', this.price) : this.free);
                return parts.join(' · ');
            },
        };
    }
</script>
@endpush
