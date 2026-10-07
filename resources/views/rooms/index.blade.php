@extends('layouts.app')

@section('title', 'Meciuri')

@section('content')
    @php
        $currentCity = $cities->firstWhere('slug', $filters['city'] ?? null);
        $query = fn (array $change) => route('rooms.index', array_filter(array_merge($filters, $change)));
    @endphp

    <div class="page-head">
        <div>
            <h1 class="display">Meciuri {{ $currentCity ? 'în '.$currentCity->name : 'deschise' }}</h1>
            <p class="muted">Alege un sport, apoi intră în meci sau spune că te interesează.</p>
        </div>
        @auth
            <a href="{{ route('rooms.create', ['sport' => $filters['sport'] ?? null]) }}" class="btn btn-ball">Creează un meci</a>
        @endauth
    </div>

    {{-- Sport cards double as the sport filter --}}
    <nav class="sport-rail" aria-label="Filtrează după sport">
        <a href="{{ $query(['sport' => null]) }}" class="sport-card" style="--c: var(--line)"
           aria-current="{{ empty($filters['sport']) ? 'true' : 'false' }}">
            <h3 class="display">Toate</h3>
            <div class="n">{{ $sports->sum('upcoming_count') }}</div>
            <small>meciuri</small>
        </a>
        @foreach ($sports as $sport)
            <a href="{{ $query(['sport' => $sport->slug]) }}" class="sport-card" style="--c: {{ $sport->color }}"
               aria-current="{{ ($filters['sport'] ?? null) === $sport->slug ? 'true' : 'false' }}">
                <h3 class="display">{{ $sport->name }}</h3>
                <div class="n">{{ $sport->upcoming_count }}</div>
                <small>{{ $sport->upcoming_count === 1 ? 'meci' : 'meciuri' }}</small>
            </a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('rooms.index') }}" class="filters">
        @if (! empty($filters['sport']))
            <input type="hidden" name="sport" value="{{ $filters['sport'] }}">
        @endif
        <div class="field">
            <label for="q">Caută</label>
            <input id="q" type="search" name="q" class="input" value="{{ $filters['q'] ?? '' }}" maxlength="80"
                   placeholder="Titlu, teren, oraș">
        </div>
        <div class="field">
            <label for="city">Oraș</label>
            <select id="city" name="city" class="input" onchange="this.form.submit()">
                <option value="">Toate orașele</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->slug }}" @selected(($filters['city'] ?? null) === $city->slug)>{{ $city->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="date">Ziua</label>
            <input id="date" type="date" name="date" class="input" value="{{ $filters['date'] ?? '' }}"
                   min="{{ now()->toDateString() }}" onchange="this.form.submit()">
        </div>
        <div class="field">
            <label for="time">De la ora</label>
            <input id="time" type="time" name="time" class="input" value="{{ $filters['time'] ?? '' }}" onchange="this.form.submit()">
        </div>
        <div class="field">
            <label for="venue">Teren</label>
            <select id="venue" name="venue" class="input" onchange="this.form.submit()">
                <option value="">Oricare</option>
                @foreach (\App\Models\Room::VENUE_TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['venue'] ?? null) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <label class="check" style="align-self:center">
            <input type="checkbox" name="free" value="1" @checked(! empty($filters['free'])) onchange="this.form.submit()"> Doar cu locuri libere
        </label>
        <button class="btn btn-line">Caută</button>
        @if (array_filter($filters))
            <a href="{{ route('rooms.index') }}" class="btn btn-quiet">Șterge filtrele</a>
        @endif
    </form>

    @if ($rooms->isEmpty())
        <div class="empty">
            <h2 class="display" style="font-size:2.2rem">Niciun meci aici încă</h2>
            <p>Schimbă orașul sau ziua, ori creează tu primul meci.</p>
            <a href="{{ route('rooms.create', ['sport' => $filters['sport'] ?? null]) }}" class="btn btn-ball">Creează un meci</a>
        </div>
    @else
        <div class="rooms">
            @foreach ($rooms as $room)
                @include('rooms.partials.card', ['room' => $room])
            @endforeach
        </div>

        @if ($rooms->hasPages())
            <div class="page-head" style="margin-top:32px">
                <span class="muted">Pagina {{ $rooms->currentPage() }} din {{ $rooms->lastPage() }}</span>
                <div style="display:flex; gap:10px">
                    @if ($rooms->previousPageUrl())
                        <a class="btn btn-quiet" href="{{ $rooms->previousPageUrl() }}">Înapoi</a>
                    @endif
                    @if ($rooms->nextPageUrl())
                        <a class="btn btn-line" href="{{ $rooms->nextPageUrl() }}">Mai multe meciuri</a>
                    @endif
                </div>
            </div>
        @endif
    @endif
@endsection
