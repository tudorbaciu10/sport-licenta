<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('landing.brand') }} — {{ __('landing.tagline') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/logo.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}">
</head>
<body>

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
                    <a id="btn-dashboard" href="{{ route('dashboard') }}" class="btn btn--primary">{{ __('landing.nav_dashboard') }}</a>
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

    {{-- ===== Section: Footer ===== --}}
    <footer id="site-footer" class="landing-footer">
        <div class="landing-container">
            {{ __('landing.footer', ['year' => now()->year]) }}
        </div>
    </footer>

    <script src="{{ asset('assets/js/landing.js') }}"></script>
</body>
</html>
