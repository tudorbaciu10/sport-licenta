@extends('layouts.app')

@section('title', 'Creează un meci')

@section('content')
    <div class="page-head">
        <div>
            <h1 class="display">Creează un meci</h1>
            <p class="muted">Tu ești primul jucător. Ceilalți se pot alătura până se umplu locurile.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('rooms.store') }}" class="panel form-stack"
          x-data="{ rules: {{ Js::from(old('rules', [''])) }} }">
        @csrf

        <div class="grid-2">
            <div class="field">
                <label for="sport_id">Sport</label>
                <select id="sport_id" name="sport_id" class="input" required>
                    <option value="">Alege sportul</option>
                    @foreach ($sports as $sport)
                        <option value="{{ $sport->id }}" @selected(old('sport_id', $selectedSport) == $sport->id)>{{ $sport->name }}</option>
                    @endforeach
                </select>
                @error('sport_id') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="city_id">Oraș</label>
                <select id="city_id" name="city_id" class="input" required>
                    <option value="">Alege orașul</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}" @selected(old('city_id') == $city->id)>{{ $city->name }}</option>
                    @endforeach
                </select>
                @error('city_id') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="field">
            <label for="title">Titlu</label>
            <input id="title" name="title" class="input" value="{{ old('title') }}" maxlength="120" required
                   placeholder="De exemplu: Minifotbal de joi seara">
            @error('title') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="grid-2">
            <div class="field">
                <label for="location_name">Locația</label>
                <input id="location_name" name="location_name" class="input" value="{{ old('location_name') }}" maxlength="160" required
                       placeholder="Teren sintetic Botanica">
                @error('location_name') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="match_date_time">Data și ora</label>
                <input id="match_date_time" type="datetime-local" name="match_date_time" class="input" required
                       value="{{ old('match_date_time') }}" min="{{ now()->format('Y-m-d\TH:i') }}">
                @error('match_date_time') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid-2">
            <div class="field">
                <label for="max_players">Câți jucători, cu tine</label>
                <input id="max_players" type="number" name="max_players" class="input" min="2" max="50" required
                       value="{{ old('max_players', 10) }}">
                @error('max_players') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="venue_type">Tipul terenului</label>
                <select id="venue_type" name="venue_type" class="input">
                    <option value="">Nu contează</option>
                    @foreach (\App\Models\Room::VENUE_TYPES as $value => $label)
                        <option value="{{ $value }}" @selected(old('venue_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('venue_type') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="field">
            <label for="description">Descriere</label>
            <textarea id="description" name="description" class="input" maxlength="2000"
                      placeholder="Nivel, echipament, cum împărțiți plata pe teren">{{ old('description') }}</textarea>
            @error('description') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label>Reguli</label>
            <template x-for="(rule, i) in rules" :key="i">
                <div class="rule-row">
                    <input class="input" name="rules[]" x-model="rules[i]" maxlength="160"
                           :aria-label="'Regula ' + (i + 1)" placeholder="De exemplu: Fără tackling">
                    <button type="button" class="btn btn-quiet" @click="rules.splice(i, 1)" x-show="rules.length > 1"
                            aria-label="Șterge regula">Șterge</button>
                </div>
            </template>
            <button type="button" class="btn btn-quiet" style="justify-self:start" @click="rules.push('')" x-show="rules.length < 10">
                Adaugă o regulă
            </button>
            @error('rules') <span class="error">{{ $message }}</span> @enderror
            @error('rules.*') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div style="display:flex; gap:12px; flex-wrap:wrap">
            <button type="submit" class="btn btn-ball">Publică meciul</button>
            <a href="{{ route('rooms.index') }}" class="btn btn-quiet">Renunță</a>
        </div>
    </form>
@endsection
