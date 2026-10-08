@extends('layouts.app')

@section('title', __('home.title'))

@php
    $cityName = $city?->label();
    $today = $cityName
        ? trans_choice('home.today', $todayCount, ['count' => $todayCount, 'city' => $cityName])
        : trans_choice('home.today_all', $todayCount, ['count' => $todayCount]);
    $roomsUrl = fn (array $q = []) => route('rooms.index', array_filter(array_merge(['city' => $city?->slug], $q)));
@endphp

@push('styles')
<style>
    /* Home only. Tokens only. */
    .home { display: grid; gap: var(--space-8); }
    .hero-row { display: grid; gap: var(--space-6); }
    .hero { display: grid; gap: var(--space-4); padding-top: var(--space-4); align-content: start; }
    .hero h1 { max-width: 18ch; }
    .hero__today { display: inline-flex; align-items: center; gap: var(--space-2); color: var(--text-2); font-size: var(--fs-callout); }
    .hero__today::before { content: ""; width: 8px; height: 8px; border-radius: var(--radius-pill); background: var(--success); }
    .hero-search { display: flex; gap: var(--space-2); max-width: 560px; }
    .hero-search .search { position: relative; flex: 1; }
    .hero-search .search .icon { position: absolute; left: var(--space-3); top: 50%; transform: translateY(-50%); width: 20px; height: 20px; color: var(--text-2); pointer-events: none; }
    .hero-search .input { padding-left: calc(var(--space-3) + 20px + var(--space-2)); }

    .section-head { display: flex; align-items: center; justify-content: space-between; gap: var(--space-3); margin-bottom: var(--space-4); flex-wrap: wrap; }
    .section-head .select .input { min-height: var(--tap); border-radius: var(--radius-pill); font-size: var(--fs-callout); font-weight: var(--fw-semibold); }
    .see-all { display: inline-flex; align-items: center; gap: var(--space-1); min-height: var(--tap); font-weight: var(--fw-medium); }
    .see-all .icon { width: 18px; height: 18px; }

    .steps { display: grid; gap: var(--space-3); counter-reset: step; }
    .step { display: grid; grid-template-columns: auto 1fr; gap: var(--space-1) var(--space-4); padding: var(--space-5); border-radius: var(--radius-card); background: var(--bg-subtle); }
    .step__num {
        grid-row: span 2; display: grid; place-items: center; width: var(--tap); height: var(--tap);
        border-radius: var(--radius-pill); background: var(--bg); color: var(--text);
        font-size: var(--fs-headline); font-weight: var(--fw-bold);
    }

    .cta { display: grid; justify-items: center; gap: var(--space-3); padding: var(--space-7) var(--space-5); border-radius: var(--radius-card); background: var(--bg-subtle); text-align: center; }
    .cta__actions { display: flex; flex-wrap: wrap; justify-content: center; gap: var(--space-3); margin-top: var(--space-2); }

    /* Phones: three upcoming matches are enough; "Vezi toate" leads to the rest */
    @media (max-width: 767px) {
        .home .match-list > :nth-child(n+4) { display: none; }
    }
    @media (min-width: 768px) {
        .steps { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .step { grid-template-columns: 1fr; }
        .step__num { grid-row: auto; margin-bottom: var(--space-2); }
    }
    @media (min-width: 1024px) {
        .hero { padding-top: var(--space-7); }
        .hero-row { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: center; }
        .hero-row__calendar { justify-self: end; }
    }
</style>
@endpush

@section('content')
<div class="home">
    {{-- First section: message + search on the left, public calendar on the right (≥1024px) --}}
    <div class="hero-row">
    <section class="hero" aria-labelledby="hero-title">
        <p class="hero__today tabular" aria-live="polite">{{ $today }}</p>
        <h1 id="hero-title" class="t-large">{{ __('home.hero_title') }}</h1>
        <p class="t-body t-secondary measure">{{ __('home.hero_text') }}</p>
        <form method="GET" action="{{ route('rooms.index') }}" class="hero-search" role="search">
            @if ($city)<input type="hidden" name="city" value="{{ $city->slug }}">@endif
            <div class="search">
                <label class="sr-only" for="home-q">{{ __('home.search_label') }}</label>
                <x-icon.search />
                <input id="home-q" type="search" name="q" class="input" maxlength="80" placeholder="{{ __('home.search_ph') }}" enterkeyhint="search" autocomplete="off">
            </div>
            <x-button type="submit">{{ __('home.search') }}</x-button>
        </form>
    </section>
    <x-calendar :city="$city" class="hero-row__calendar" />
    </div>

    {{-- "Ce joci azi?" — the colourful sport cards are the one big idea --}}
    <section aria-labelledby="pick-title">
        <div class="section-head">
            <h2 id="pick-title" class="t-title1">{{ __('home.pick_title') }}</h2>
            <form method="GET" action="{{ route('home') }}">
                <label class="sr-only" for="home-city">{{ __('home.city') }}</label>
                <div class="select">
                    <select id="home-city" name="city" class="input" onchange="this.form.submit()">
                        @foreach ($cities as $c)
                            <option value="{{ $c->slug }}" @selected($city?->is($c))>{{ $c->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <noscript><x-button variant="secondary" size="sm" type="submit">{{ __('rooms.index.apply') }}</x-button></noscript>
            </form>
        </div>
        <div class="sport-grid">
            @foreach ($sports as $sport)
                <x-sport-card :sport="$sport" :count="$sport->open_count" :href="$roomsUrl(['sport' => $sport->slug])" />
            @endforeach
        </div>
    </section>

    {{-- Real upcoming matches (no invented numbers) --}}
    <section aria-labelledby="upcoming-title">
        <div class="section-head">
            <h2 id="upcoming-title" class="t-title1">{{ $cityName ? __('home.upcoming_title', ['city' => $cityName]) : __('home.upcoming_title_all') }}</h2>
            @if ($upcoming->isNotEmpty())
                <a class="see-all" href="{{ $roomsUrl() }}">{{ __('home.see_all') }} <x-icon.chevron-right /></a>
            @endif
        </div>
        @if ($upcoming->isEmpty())
            <x-empty-state :title="__('home.empty_title')" :text="__('home.empty_text')">
                <x-button :href="route('rooms.create')" icon="circle-plus">{{ __('home.empty_action') }}</x-button>
            </x-empty-state>
        @else
            <div class="match-list">
                @foreach ($upcoming as $room)
                    <x-match-card :room="$room" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- How it works: a real sequence, so numbered --}}
    <section aria-labelledby="how-title">
        <h2 id="how-title" class="t-title1" style="margin-bottom: var(--space-4)">{{ __('home.how_title') }}</h2>
        <ol class="steps" role="list">
            @foreach (__('home.steps') as $i => [$title, $text])
                <li class="step">
                    <span class="step__num" aria-hidden="true">{{ $i + 1 }}</span>
                    <h3 class="t-headline">{{ $title }}</h3>
                    <p class="t-callout t-secondary">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="cta" aria-labelledby="cta-title">
        <h2 id="cta-title" class="t-title1">{{ __('home.cta_title') }}</h2>
        @guest
            <p class="t-body t-secondary">{{ __('home.cta_text') }}</p>
            <div class="cta__actions">
                <x-button :href="route('register')">{{ __('home.cta_guest') }}</x-button>
                <x-button variant="secondary" :href="route('login')">{{ __('home.cta_login') }}</x-button>
            </div>
        @else
            <div class="cta__actions">
                <x-button :href="route('rooms.create')" icon="circle-plus">{{ __('home.cta_member') }}</x-button>
            </div>
        @endguest
    </section>
</div>
@endsection
