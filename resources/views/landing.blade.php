<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('landing.brand') }} — {{ __('landing.tagline') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/logo.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}">
</head>
@php($openEvent = $openEvent ?? null)
<body class="{{ $openEvent ? 'has-panel' : '' }}">

    {{-- ===== Section: Site header / navbar ===== --}}
    <header id="site-header" class="landing-header">
        <div class="landing-container landing-header__inner">
            <a href="{{ route('landing') }}" class="landing-header__brand">
                <img src="{{ asset('assets/images/logo.svg') }}" alt="{{ __('landing.brand') }}" width="40" height="40">
                <span>
                    {{ __('landing.brand') }}
                    <small>{{ __('landing.tagline') }}</small>
                </span>
            </a>

            <div class="landing-header__actions">
                {{-- Language switcher (ro / en / ru) --}}
                <nav id="language-switch" class="lang-switch" aria-label="Language">
                    @foreach (['ro' => 'RO', 'en' => 'EN', 'ru' => 'RU'] as $code => $label)
                        <a id="btn-lang-{{ $code }}" href="{{ route('lang.switch', $code) }}"
                           class="{{ app()->getLocale() === $code ? 'is-active' : '' }}">{{ $label }}</a>
                    @endforeach
                </nav>

                @auth
                    <button type="button" id="btn-create-event" class="btn btn--primary"
                            data-panel-url="{{ route('events.create-form') }}">{{ __('landing.nav_create') }}</button>

                    <div class="user-menu" id="user-menu">
                        <button type="button" class="user-menu__trigger" id="user-menu-trigger" aria-haspopup="true" aria-expanded="false">
                            <span class="user-menu__avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="user-menu__name">{{ auth()->user()->name }}</span>
                            <span class="user-menu__caret">▾</span>
                        </button>
                        <div class="user-menu__dropdown" id="user-menu-dropdown">
                            <button type="button" class="user-menu__item" data-panel-url="{{ route('profile.details.form') }}">{{ __('landing.menu_edit_profile') }}</button>
                            <a class="user-menu__item" href="{{ route('dashboard') }}">{{ __('landing.menu_my_events') }}</a>
                            <a class="user-menu__item" href="{{ route('venues.mine') }}">{{ __('landing.menu_my_facilities') }}</a>
                            <a class="user-menu__item" href="{{ route('profile.edit') }}">{{ __('landing.menu_account') }}</a>
                            @if (auth()->user()->isAdmin())
                                <a class="user-menu__item" href="{{ route('admin.dashboard') }}">Admin</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="user-menu__item user-menu__item--danger">{{ __('landing.menu_logout') }}</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a id="btn-login" href="{{ route('login') }}" class="btn btn--ghost">{{ __('landing.nav_login') }}</a>
                    <a id="btn-register" href="{{ route('register') }}" class="btn btn--primary">{{ __('landing.nav_register') }}</a>
                @endauth
            </div>
        </div>
    </header>

    {{-- ===== Section: Hero ===== --}}
    <!-- <section id="hero" class="landing-hero">
        <div class="landing-container landing-hero__inner">
            <h1 class="landing-hero__title">{{ __('landing.hero_title') }}</h1>
            <p class="landing-hero__subtitle">{{ __('landing.hero_subtitle') }}</p>
            <a href="#rooms" class="btn btn--primary">{{ __('landing.hero_cta') }}</a>
        </div>
        <div class="landing-hero__stripes" aria-hidden="true"><span></span><span></span><span></span></div>
    </section> -->

    {{-- ===== Section: Sport selector (horizontal scroll) ===== --}}
    <nav id="sport-selector" class="sport-selector" aria-label="{{ __('landing.sports_heading') }}">
        <div class="landing-container sport-selector__inner">
            <span class="sport-selector__label">{{ __('landing.sports_heading') }}:</span>
            <div id="sport-track" class="sport-selector__track">
                <a id="chip-sport-all" href="{{ route('landing') }}" data-sport=""
                   class="sport-chip {{ $selectedSport ? '' : 'is-active' }}">
                    {{ __('landing.sports_all') }}
                </a>
                @foreach ($sports as $sport)
                    <a id="chip-sport-{{ $sport->id }}" href="{{ route('landing', ['sport' => $sport->id]) }}" data-sport="{{ $sport->id }}"
                       class="sport-chip {{ $selectedSport === $sport->id ? 'is-active' : '' }}">
                        {{ $sport->name }}
                        <span class="sport-chip__count">{{ $sport->events_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </nav>

    {{-- ===== Section: Room filters (calendar date range / time / city) ===== --}}
    <section id="room-filters" class="room-filters" aria-label="{{ __('landing.filters_heading') }}">
        <div class="landing-container">
            <form id="room-filters-form" class="room-filters__form" method="GET" action="{{ route('landing') }}">
                {{-- keep the currently selected sport when submitting without JS --}}
                <input type="hidden" id="filter-sport" name="sport" value="{{ $selectedSport }}">

                <span class="room-filters__title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                        <path d="M16 2v4M8 2v4M3 10h18"></path>
                    </svg>
                    {{ __('landing.filters_heading') }}
                </span>

                <div class="filter-field">
                    <label for="filter-date-from">{{ __('landing.filter_date_from') }}</label>
                    <input type="date" id="filter-date-from" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="filter-field">
                    <label for="filter-date-to">{{ __('landing.filter_date_to') }}</label>
                    <input type="date" id="filter-date-to" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="filter-field">
                    <label for="filter-time-from">{{ __('landing.filter_time_from') }}</label>
                    <input type="time" id="filter-time-from" name="time_from" value="{{ $filters['time_from'] ?? '' }}">
                </div>
                <div class="filter-field">
                    <label for="filter-city">{{ __('landing.filter_city') }}</label>
                    <input type="text" id="filter-city" name="city" value="{{ $filters['city'] ?? '' }}"
                           placeholder="{{ __('landing.filter_city') }}">
                </div>

                <div class="room-filters__actions">
                    <button type="submit" id="btn-filter-apply" class="btn btn--primary">{{ __('landing.filter_apply') }}</button>
                    <button type="button" id="btn-filter-reset" class="btn btn--red">{{ __('landing.filter_reset') }}</button>
                </div>
            </form>
        </div>
    </section>

    {{-- ===== Section: Rooms (already-created events from the database) ===== --}}
    <section id="rooms" class="rooms-section">
        <div class="landing-container">
            <div class="rooms-section__head">
                <h2>{{ __('landing.rooms_heading') }}</h2>
                <span class="rooms-section__count">
                    <span id="rooms-count">{{ $events->count() }}</span> {{ __('landing.rooms_word') }}
                </span>
            </div>

            <div id="rooms-list" class="rooms-grid" data-rooms-url="{{ route('landing.rooms') }}">
                @include('landing.partials.rooms')
            </div>
        </div>
    </section>

    {{-- ===== Section: Facilities selector (horizontal scroll of categories) ===== --}}
    <nav id="venue-selector" class="sport-selector venue-selector" aria-label="{{ __('venues.categories_heading') }}">
        <div class="landing-container sport-selector__inner">
            <span class="sport-selector__label">🏟️ {{ __('venues.categories_heading') }}:</span>
            <div id="venue-track" class="sport-selector__track">
                <a id="chip-venue-all" href="{{ route('landing') }}" data-category=""
                   class="sport-chip {{ $selectedCategory ? '' : 'is-active' }}">
                    {{ __('venues.categories_all') }}
                </a>
                @foreach ($categories as $category)
                    <a id="chip-venue-{{ $category->id }}" href="{{ route('landing', ['category' => $category->id]) }}" data-category="{{ $category->id }}"
                       class="sport-chip {{ $selectedCategory === $category->id ? 'is-active' : '' }}">
                        {{ $category->icon }} {{ $category->name }}
                        <span class="sport-chip__count">{{ $category->venues_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </nav>

    {{-- ===== Section: Facility filters (city / country / surface) ===== --}}
    <section id="venue-filters" class="room-filters" aria-label="{{ __('venues.section_heading') }}">
        <div class="landing-container">
            <form id="venue-filters-form" class="room-filters__form" method="GET" action="{{ route('landing') }}">
                <input type="hidden" id="filter-category" name="category" value="{{ $selectedCategory }}">

                <span class="room-filters__title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    {{ __('venues.filter_city') }}
                </span>

                <div class="filter-field">
                    <label for="filter-venue-city">{{ __('venues.filter_city') }}</label>
                    <input type="text" id="filter-venue-city" name="venue_city" value="{{ $venueFilters['venue_city'] ?? '' }}"
                           placeholder="Chișinău / Bălți">
                </div>
                <div class="filter-field">
                    <label for="filter-venue-country">{{ __('venues.filter_country') }}</label>
                    <input type="text" id="filter-venue-country" name="venue_country" value="{{ $venueFilters['venue_country'] ?? '' }}">
                </div>
                <div class="filter-field">
                    <label for="filter-surface">{{ __('venues.filter_surface') }}</label>
                    <select id="filter-surface" name="surface"
                            class="filter-select">
                        <option value="">{{ __('venues.filter_surface_any') }}</option>
                        @foreach (['synthetic', 'grass', 'hard', 'parquet'] as $surface)
                            <option value="{{ $surface }}" @selected(($venueFilters['surface'] ?? '') === $surface)>
                                {{ __('venues.surface_'.$surface) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="room-filters__actions">
                    <button type="submit" id="btn-venue-filter-apply" class="btn btn--primary">{{ __('venues.filter_apply') }}</button>
                    <button type="button" id="btn-venue-filter-reset" class="btn btn--red">{{ __('venues.filter_reset') }}</button>
                </div>
            </form>
        </div>
    </section>

    {{-- ===== Section: Facilities grid (rentable venues from the database) ===== --}}
    <section id="venues" class="rooms-section">
        <div class="landing-container">
            <div class="rooms-section__head">
                <div>
                    <h2>{{ __('venues.section_heading') }}</h2>
                    <p class="venues-subtitle">{{ __('venues.section_subtitle') }}</p>
                </div>
                <span class="rooms-section__count">
                    <span id="venues-count">{{ $venues->count() }}</span> {{ __('venues.count_word') }}
                </span>
            </div>

            <div id="venues-list" class="rooms-grid" data-venues-url="{{ route('landing.venues') }}">
                @include('landing.partials.venues')
            </div>
        </div>
    </section>

    {{-- ===== Section: Footer ===== --}}
    <footer id="site-footer" class="landing-footer">
        <div class="landing-container">
            {{ __('landing.footer', ['year' => now()->year]) }}
        </div>
    </footer>

    {{-- ===== Slide-over panel (event detail / create event / edit profile) ===== --}}
    <div id="slideover-overlay" class="slideover-overlay {{ $openEvent ? 'is-open' : '' }}" @unless($openEvent) hidden @endunless></div>
    <aside id="slideover" class="slideover {{ $openEvent ? 'is-open' : '' }}"
           @unless($openEvent) hidden @endunless
           aria-hidden="{{ $openEvent ? 'false' : 'true' }}"
           @if($openEvent) data-open-room="{{ route('landing.room', $openEvent) }}" @endif>
        <div class="slideover__grabber" data-panel-close aria-hidden="true"></div>
        <div id="slideover-body" class="slideover__body">@if($openEvent)@include('landing.partials.room-detail', ['event' => $openEvent])@endif</div>
    </aside>
    <div id="toast" class="toast" hidden></div>

    <script src="{{ asset('assets/js/landing.js') }}"></script>
    <script src="{{ asset('assets/js/venues.js') }}"></script>
    <script src="{{ asset('assets/js/app-shell.js') }}"></script>
</body>
</html>
