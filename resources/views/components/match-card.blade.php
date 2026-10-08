{{-- <x-match-card :room="$room" />
     Expects sport and city loaded. Time is the strongest element; sport colour only in the band and icon. --}}
@props(['room'])
@php
    $left = $room->spotsLeft();
    $pct = $room->max_players ? min(100, round($room->current_players_count / $room->max_players * 100)) : 0;
    $when = $room->match_date_time->locale(app()->getLocale());
@endphp
<a href="{{ route('rooms.show', $room) }}" {{ $attributes->class('match-card') }} style="--c: var({{ $room->sport->cssVar() }})">
    <div class="match-card__head">
        <x-sport-icon :sport="$room->sport" size="md" />
        <div class="match-card__when">
            <time class="t-title2 tabular" datetime="{{ $room->match_date_time->toIso8601String() }}">{{ $when->format('H:i') }}</time>
            <span class="t-footnote t-secondary">{{ $when->isoFormat('ddd, D MMM') }} · {{ $room->sport->label() }}</span>
        </div>
        <x-badge :status="$room->availability()" class="match-card__badge" />
    </div>

    <div class="match-card__body">
        <h3 class="t-headline match-card__title">{{ $room->title }}</h3>
        <p class="t-callout t-secondary match-card__meta">
            <x-icon.map-pin class="icon icon--xs" />
            <span>{{ $room->location_name }}, {{ $room->city->label() }}@if ($room->venue_type) · {{ __('rooms.index.'.$room->venue_type) }}@endif</span>
        </p>
    </div>

    <div class="match-card__spots">
        <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $room->max_players }}"
             aria-valuenow="{{ $room->current_players_count }}"
             aria-label="{{ __('components.match.players', ['taken' => $room->current_players_count, 'max' => $room->max_players]) }}">
            <span style="width: {{ $pct }}%"></span>
        </div>
        <span class="t-footnote tabular match-card__count">
            <x-icon.users class="icon icon--xs" />
            {{ __('components.match.taken', ['taken' => $room->current_players_count, 'max' => $room->max_players]) }}
            · {{ trans_choice('components.match.spots_left', $left, ['count' => $left]) }}
        </span>
    </div>
</a>
