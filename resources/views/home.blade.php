@extends('layouts.app')

@section('title', __('home.title'))

@php
    $cityName = $city?->label();
    $roomsUrl = fn (array $q = []) => route('rooms.index', array_filter(array_merge(['city' => $city?->slug], $q)));
@endphp

@push('styles')
<style>
    /* Home only. Tokens only. */
    .home { display: grid; gap: var(--space-8); }
    .first { display: grid; gap: var(--space-5); }
    .pick-row { display: grid; gap: var(--space-6); }
    .pick-row > * { min-width: 0; }   /* lets the phone sports rail scroll instead of widening the page */
    .pick-row__calendar { justify-self: center; }   /* under 1024px the calendar sits alone below the sports */
    .hero-search { display: flex; gap: var(--space-2); }   /* the search bar alone, full width */
    .hero-search .search { position: relative; flex: 1; }
    .hero-search .search .icon { position: absolute; left: var(--space-3); top: 50%; transform: translateY(-50%); width: 20px; height: 20px; color: var(--text-2); pointer-events: none; }
    .hero-search .input { padding-left: calc(var(--space-3) + 20px + var(--space-2)); }

    .section-head { display: flex; align-items: center; justify-content: space-between; gap: var(--space-3); margin-bottom: var(--space-3); flex-wrap: wrap; }
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

    /* "Toate" tile: neutral, only in the phone rail */
    .sport-card--all { display: none; --c: var(--text-2); }
    .sport-card--all .sport-icon { --c: var(--bg); --on: var(--text); }

    .pick-row__calendar { min-width: 0; }   /* inside the sheet wrapper (display: contents on laptops) */

    /* Phones (Figma Home): hello + city, chips, calendar banner */
    .home-hello { display: flex; align-items: center; justify-content: space-between; gap: var(--space-3); }
    .home-hello__text { display: grid; min-width: 0; }
    .home-hello__city { display: flex; align-items: center; gap: var(--space-1); color: var(--text-2); }
    .home > *, .first > *, .home-sports { min-width: 0; }   /* the chips row scrolls instead of widening the page */
    .home-sports .section-head { margin-bottom: var(--space-2); }
    .home-banner {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: var(--space-3);
        padding: var(--space-4) var(--space-5); border-radius: var(--radius-card); background: var(--bg-subtle);
    }
    .home-banner__text { display: grid; gap: 2px; flex: 1 1 160px; min-width: 0; }
    .home-banner .btn { flex: none; }
    @media (max-width: 1023px) {
        .home { gap: var(--space-7); }
        .pick-row { display: contents; }   /* only the (closed) calendar sheet is left in it on phones */
    }

    /* Phones: three upcoming matches are enough; "Vezi toate" leads to the rest */
    @media (max-width: 767px) {
        .home .match-list > :nth-child(n+4) { display: none; }

        /* Sports as one horizontal row, edge to edge, snapping tile by tile */
        .sport-rail {
            grid-template-columns: none; grid-auto-flow: column; grid-auto-columns: 124px; gap: var(--space-2);
            overflow-x: auto; overscroll-behavior-x: contain; scroll-snap-type: x mandatory;
            margin-inline: calc(-1 * var(--gutter)); padding: var(--space-1) var(--gutter);
            scroll-padding-inline: var(--gutter); scrollbar-width: none;
        }
        .sport-rail::-webkit-scrollbar { display: none; }
        .sport-rail > .sport-card { scroll-snap-align: start; flex-direction: column; align-items: flex-start; justify-content: space-between; gap: var(--space-2); min-height: 116px; }
        .sport-rail .sport-card--all { display: flex; }
        .sport-rail .t-headline { overflow-wrap: normal; hyphens: manual; }   /* "Badminton" stays whole; "Tenis de masă" wraps at spaces */
    }
    @media (min-width: 768px) {
        .steps { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .step { grid-template-columns: 1fr; }
        .step__num { grid-row: auto; margin-bottom: var(--space-2); }
    }
    @media (min-width: 1024px) {
        /* Sports and calendar side by side, top-aligned, so both fit in the first screen */
        .pick-row { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; gap: var(--space-6); }
        .pick-row .pick-row__calendar { justify-self: stretch; max-width: none; }   /* fills the right column, aligned with the search button */
        .sport-grid--compact { grid-template-columns: repeat(2, minmax(0, 1fr)); }   /* half width next to the calendar */

        /* 20 sports: scroll inside a box as tall as the calendar; the faded bottom edge says there is more */
        .sport-rail {
            max-height: 381px; overflow-y: auto; overscroll-behavior: contain;   /* bottom lines up with the calendar */
            padding: var(--space-1) var(--space-2) var(--space-7) 0; scrollbar-width: thin;
            -webkit-mask-image: linear-gradient(to bottom, #000 80%, transparent);
            mask-image: linear-gradient(to bottom, #000 80%, transparent);
        }
    }
</style>
@endpush

@section('content')
<div class="home">
    {{-- First screen: search bar alone on top; below it the sports (left) and the calendar (right) from 1024px --}}
    <div class="first">
        {{-- Only the search bar is visible; the page title stays for screen readers --}}
        <h1 class="sr-only">{{ __('home.hero_title') }}</h1>

        {{-- Phones (Figma Home): hello + city, chat or login on the right --}}
        <header class="home-hello m-only">
            <div class="home-hello__text">
                <p class="t-title1">{{ auth()->check() ? __('home.greeting', ['name' => \Illuminate\Support\Str::before(trim(auth()->user()->name), ' ')]) : __('home.greeting_guest') }}</p>
                <div class="home-hello__city">
                    <x-icon.map-pin class="icon icon--xs" />
                    <x-dropdown class="dropdown--plain" align="start" :label="__('home.city')" :current="$city?->label() ?? __('home.all_cities')"
                                :items="$cities->map(fn ($c) => ['label' => $c->label(), 'href' => route('home', ['city' => $c->slug]), 'active' => $city?->is($c)])->all()" />
                </div>
            </div>
            @if ($chatLink = \App\Support\Navigation::chat())
                <a href="{{ $chatLink['url'] }}" class="icon-btn" aria-label="{{ trans_choice('chat.nav_unread', $chatLink['unread'], ['count' => $chatLink['unread']]) }}">
                    <x-icon.message-circle />
                    @if ($chatLink['unread'])<span class="count-badge" aria-hidden="true">{{ min(99, $chatLink['unread']) }}</span>@endif
                </a>
            @elseif (auth()->guest())
                <x-button variant="secondary" size="sm" :href="route('login')">{{ __('home.login') }}</x-button>
            @endif
        </header>
            <form method="GET" action="{{ route('rooms.index') }}" class="hero-search" role="search">
                @if ($city)<input type="hidden" name="city" value="{{ $city->slug }}">@endif
                <div class="search">
                    <label class="sr-only" for="home-q">{{ __('home.search_label') }}</label>
                    <x-icon.search />
                    <input id="home-q" type="search" name="q" class="input" maxlength="80" placeholder="{{ __('home.search_ph') }}" enterkeyhint="search" autocomplete="off">
                </div>
                <x-button type="submit" class="d-only">{{ __('home.search') }}</x-button>
            </form>

        {{-- Phones: sports as one row of chips and a banner that opens the calendar in a sheet --}}
        <section class="home-sports m-only" aria-labelledby="home-sports-title">
            <div class="section-head">
                <h2 id="home-sports-title" class="t-title2">{{ __('home.sports_title') }}</h2>
                <a class="see-all" href="{{ $roomsUrl() }}">{{ __('home.sports_all') }}</a>
            </div>
            <nav class="chips" aria-label="{{ __('home.sports_title') }}">
                <x-chip :href="$roomsUrl()" active>{{ __('home.all_sports') }}</x-chip>
                @foreach ($sports as $sport)
                    <x-chip :href="$roomsUrl(['sport' => $sport->slug])" :sport="$sport">{{ $sport->label() }}</x-chip>
                @endforeach
            </nav>
        </section>

        <div class="home-banner m-only">
            <div class="home-banner__text">
                <p class="t-headline">{{ __('home.banner_title') }}</p>
                <p class="t-footnote t-secondary">{{ __('home.banner_text') }}</p>
            </div>
            <button type="button" class="btn btn--primary btn--sm" x-data x-on:click="$dispatch('open-sheet', 'home-calendar')">
                <x-icon.calendar-days class="icon icon--sm" /> {{ __('home.banner_action') }}
            </button>
        </div>

        <div class="pick-row">
            {{-- "Ce joci azi?" — compact sport cards, real counts for the chosen city --}}
            <section class="pick d-only" aria-labelledby="pick-title">
                <div class="section-head">
                    <h2 id="pick-title" class="t-title2">{{ __('home.pick_title') }}</h2>
                <x-dropdown :label="__('home.city')" :current="$city?->label() ?? __('home.all_cities')"
                            :items="$cities->map(fn ($c) => ['label' => $c->label(), 'href' => route('home', ['city' => $c->slug]), 'active' => $city?->is($c)])->all()" />
                </div>
                {{-- Phones: one row you swipe sideways ("Toate" first), like 999.md. Wider screens: 2-column grid next to the calendar. --}}
                <div class="sport-grid sport-grid--compact sport-rail">
                    <a href="{{ $roomsUrl() }}" class="sport-card sport-card--compact sport-card--all">
                        <span class="sport-icon sport-icon--md" aria-hidden="true"><x-icon.ellipsis class="icon" /></span>
                        <span class="sport-card__text">
                            <span class="t-headline">{{ __('home.all_sports') }}</span>
                            <span class="t-footnote sport-card__count">{{ trans_choice('components.sport_card.open_matches_short', $sports->sum('open_count'), ['count' => $sports->sum('open_count')]) }}</span>
                        </span>
                    </a>
                    @foreach ($sports as $sport)
                        <x-sport-card :sport="$sport" :count="$sport->open_count" :href="$roomsUrl(['sport' => $sport->slug])" compact />
                    @endforeach
                </div>
            </section>

            {{-- Laptops: next to the sports. Phones: in a sheet opened from the banner --}}
            <x-sheet name="home-calendar" :title="__('home.calendar_title')" inline-desktop>
                <x-calendar :city="$city" class="pick-row__calendar" />
            </x-sheet>
        </div>
    </div>

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
                    <x-match-card :room="$room" hero />
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
