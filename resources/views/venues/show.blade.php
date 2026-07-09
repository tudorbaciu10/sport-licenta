<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $venue->name }} — {{ __('landing.brand') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/logo.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}">
</head>
<body>

    {{-- ===== Section: Site header ===== --}}
    <header id="site-header" class="landing-header">
        <div class="landing-container landing-header__inner">
            <a href="{{ route('landing') }}" class="landing-header__brand">
                <img src="{{ asset('assets/images/logo.svg') }}" alt="{{ __('landing.brand') }}" width="40" height="40">
                <span>{{ __('landing.brand') }}</span>
            </a>
            <div class="landing-header__actions">
                @auth
                    <a id="btn-dashboard" href="{{ route('dashboard') }}" class="btn btn--primary">{{ __('landing.nav_dashboard') }}</a>
                @else
                    <a id="btn-login" href="{{ route('login') }}" class="btn btn--ghost">{{ __('landing.nav_login') }}</a>
                    <a id="btn-register" href="{{ route('register') }}" class="btn btn--primary">{{ __('landing.nav_register') }}</a>
                @endauth
            </div>
        </div>
    </header>

    {{-- ===== Section: Facility detail ===== --}}
    <section id="venue-detail" class="rooms-section">
        <div class="landing-container" style="max-width: 860px;">
            <a href="{{ route('landing') }}#venues" class="venue-back">&larr; {{ __('venues.show_back') }}</a>

            @if (session('status'))
                <div class="landing-flash">{{ session('status') }}</div>
            @endif

            <article class="venue-detail__card">
                <div class="venue-detail__media">
                    <img src="{{ $venue->photoUrl() }}" alt="{{ $venue->name }}">
                    @if ($venue->surface)
                        @php $surfaceKey = 'venues.surface_'.$venue->surface; @endphp
                        <span class="venue-card__surface">{{ __($surfaceKey) === $surfaceKey ? ucfirst($venue->surface) : __($surfaceKey) }}</span>
                    @endif
                </div>

                <div class="venue-detail__body">
                    <div class="venue-card__top">
                        <span class="venue-card__category">{{ $venue->category?->name }}</span>
                        <span class="venue-card__inout">{{ $venue->is_indoor ? __('venues.card_indoor') : __('venues.card_outdoor') }}</span>
                    </div>

                    <h1 class="venue-detail__title">{{ $venue->name }}</h1>
                    <p class="venue-card__meta">📍 {{ $venue->city }}@if ($venue->locality), {{ $venue->locality }}@endif · {{ $venue->country }}</p>

                    @if ($venue->description)
                        <p class="venue-detail__desc">{{ $venue->description }}</p>
                    @endif

                    <dl class="venue-detail__grid">
                        <div><dt>{{ __('venues.card_price') }}</dt><dd>{{ $venue->price_per_hour ? (int) $venue->price_per_hour.' MDL' : __('venues.card_price_na') }}</dd></div>
                        @if ($venue->capacity)<div><dt>{{ __('venues.show_capacity') }}</dt><dd>{{ $venue->capacity }}</dd></div>@endif
                        @if ($venue->address)<div><dt>{{ __('venues.show_address') }}</dt><dd>{{ $venue->address }}</dd></div>@endif
                        @if ($venue->owner)<div><dt>{{ __('venues.card_owner') }}</dt><dd>{{ $venue->owner->name }}</dd></div>@endif
                    </dl>

                    {{-- Contact — visible to authenticated users only --}}
                    <div class="venue-detail__contact">
                        <h2>{{ __('venues.show_contact') }}</h2>
                        @auth
                            <p>@if ($venue->contact_phone)<b>{{ __('venues.show_phone') }}:</b> {{ $venue->contact_phone }}@endif</p>
                            <p>@if ($venue->contact_email)<b>{{ __('venues.show_email') }}:</b> {{ $venue->contact_email }}@endif</p>
                        @else
                            <p class="venue-detail__hint">{{ __('venues.show_login_hint') }}
                                <a href="{{ route('login') }}" class="btn btn--red" style="margin-left:.5rem;">{{ __('landing.nav_login') }}</a>
                            </p>
                        @endauth
                    </div>

                    @can('update', $venue)
                        <div style="margin-top:1rem;">
                            <a href="{{ route('venues.edit', $venue) }}" class="btn btn--primary">{{ __('venues.edit_heading') }}</a>
                        </div>
                    @endcan
                </div>
            </article>
        </div>
    </section>

    <footer id="site-footer" class="landing-footer">
        <div class="landing-container">{{ __('landing.footer', ['year' => now()->year]) }}</div>
    </footer>
</body>
</html>
