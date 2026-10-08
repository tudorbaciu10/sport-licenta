{{-- New app layout (redesign). Pages move here one by one; layouts/app stays for
     the not-yet-redesigned pages until Faza 5, then this replaces it. --}}
@php
    $nav = [
        ['key' => 'home',       'url' => url('/'),                 'icon' => 'house',         'active' => request()->is('/')],
        ['key' => 'search',     'url' => route('rooms.index'),     'icon' => 'search',        'active' => request()->routeIs('rooms.index', 'rooms.show')],
        ['key' => 'create',     'url' => route('rooms.create'),    'icon' => 'circle-plus',   'active' => request()->routeIs('rooms.create')],
        ['key' => 'my_matches', 'url' => route('dashboard'),       'icon' => 'calendar-days', 'active' => request()->routeIs('dashboard')],
        ['key' => 'profile',    'url' => auth()->check() ? route('dashboard') : route('login'), 'icon' => 'user-round', 'active' => request()->routeIs('login', 'register')],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') – @endif{{ config('app.name', 'Sport.md') }}</title>
    <link rel="preload" href="{{ asset('assets/fonts/inter-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/base.css') }}">
    @stack('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</head>
<body>
<a class="skip-link" href="#main">{{ __('ui.skip_to_content') }}</a>

<header class="app-header">
    <div class="container app-header__inner">
        <a href="{{ url('/') }}" class="logo">Sport<b>.md</b></a>

        <nav class="top-nav" aria-label="{{ __('ui.main_nav') }}">
            @foreach ($nav as $item)
                <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif>{{ __('ui.nav.'.$item['key']) }}</a>
            @endforeach
        </nav>

        <div class="header-actions">
            <div class="locale-switch" role="group" aria-label="{{ __('ui.language') }}">
                @foreach (\App\Http\Middleware\SetLocale::SUPPORTED as $locale)
                    <a href="{{ route('locale', $locale) }}" lang="{{ $locale }}" hreflang="{{ $locale }}"
                       aria-current="{{ app()->getLocale() === $locale ? 'true' : 'false' }}">{{ strtoupper($locale) }}</a>
                @endforeach
            </div>
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn--ghost btn--sm">{{ __('ui.auth.logout') }}</button>
                </form>
            @endauth
        </div>
    </div>
</header>

<main id="main" class="app-main" tabindex="-1">
    <div class="container">
        @if (session('status'))
            <div class="flash" role="status"><x-icon.check /> <span>{{ session('status') }}</span></div>
        @endif
        @yield('content')
    </div>
</main>

<nav class="bottom-nav" aria-label="{{ __('ui.main_nav') }}">
    @foreach ($nav as $item)
        <a href="{{ $item['url'] }}" @class(['bottom-nav__create' => $item['key'] === 'create']) @if ($item['active']) aria-current="page" @endif>
            <x-dynamic-component :component="'icon.'.$item['icon']" />
            <span @if ($item['key'] === 'my_matches') aria-hidden="true" @endif>{{ __($item['key'] === 'my_matches' ? 'ui.nav_short.my_matches' : 'ui.nav.'.$item['key']) }}</span>
            @if ($item['key'] === 'my_matches')<span class="sr-only">{{ __('ui.nav.my_matches') }}</span>@endif
        </a>
    @endforeach
</nav>
@stack('scripts')
</body>
</html>
