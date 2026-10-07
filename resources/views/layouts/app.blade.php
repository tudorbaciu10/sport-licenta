<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Meciuri') – Sport.md</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Libre+Franklin:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</head>
<body>
<header class="top">
    <div class="wrap">
        <a href="{{ url('/') }}" class="brand display">Sport<b>.md</b></a>
        <nav>
            <a href="{{ route('rooms.index') }}">Meciuri</a>
            @auth
                <a href="{{ route('rooms.create') }}" class="btn btn-ball">Creează meci</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="linkish" type="submit">Ieși</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hide-sm">Intră în cont</a>
                <a href="{{ route('register') }}" class="btn btn-ball">Creează cont</a>
            @endauth
        </nav>
    </div>
</header>

<main>
    <div class="wrap">
        @if (session('status'))
            <div class="flash" role="status">{{ session('status') }}</div>
        @endif
        @error('room')
            <div class="flash err" role="alert">{{ $message }}</div>
        @enderror

        @yield('content')
    </div>
</main>
</body>
</html>
