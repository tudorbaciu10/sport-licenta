{{-- <x-match-row :room="$room" />                                   compact row (Figma "Venue Row"), phones
     <x-match-row :room="$room" square="date" role="joined" :unread="2" />  "Meciurile mele": date in the square, my role, unread chat
     Expects sport and city loaded. The sport shows as tint, icon AND name. On a match with a pin, the place text
     is swapped for the distance by public/assets/js/distance.js once the browser has location access. --}}
@props(['room', 'square' => 'time', 'role' => null, 'unread' => 0])
@php
    $when = $room->match_date_time->locale(app()->getLocale());
    $left = $room->spotsLeft();
    $status = $room->availability();
    $price = $room->price ? __('match.price.amount', ['price' => $room->price]) : __('match.price.free');
    $place = $square === 'date' ? $room->location_name : $room->city->label();
@endphp
<a href="{{ route('rooms.show', $room) }}" {{ $attributes->class('match-row') }} style="--c: var({{ $room->sport->cssVar() }})">
    <span class="match-row__square" aria-hidden="true">
        <span class="match-row__day">{{ \Illuminate\Support\Str::upper(rtrim($when->isoFormat('ddd'), '.')) }}</span>
        <span class="match-row__big tabular">{{ $square === 'date' ? $when->format('j') : $when->format('H:i') }}</span>
    </span>

    <span class="match-row__body">
        <span class="match-row__title">{{ $room->title }}</span>
        <span class="match-row__meta">
            <x-sport-icon :sport="$room->sport" size="xs" />
            <span class="sr-only">{{ $when->isoFormat('dddd, D MMMM, H:mm') }} · </span>
            <span>{{ $room->sport->label() }}</span>
            @if ($square === 'date')<span aria-hidden="true">·</span> <time class="tabular" datetime="{{ $room->match_date_time->toIso8601String() }}">{{ $when->format('H:i') }}</time>@endif
            <span aria-hidden="true">·</span>
            @if ($room->hasPin())
                <span class="match-row__place" data-distance data-lat="{{ $room->latitude }}" data-lng="{{ $room->longitude }}">{{ $place }}</span>
            @else
                <span class="match-row__place">{{ $place }}</span>
            @endif
        </span>
        <span class="match-row__tags">
            <span class="match-row__price">{{ $price }}</span>
            @if ($role)
                <span class="match-row__role match-row__role--{{ $role }}">{{ __('profile.matches.role.'.$role) }}</span>
            @endif
            <x-badge :status="$status">{{ $status === 'full' ? '' : trans_choice('components.match.spots_left', $left, ['count' => $left]) }}</x-badge>
        </span>
    </span>

    <span class="match-row__end">
        @if ($unread > 0)
            <span class="match-row__unread">{{ min(99, $unread) }}</span><span class="sr-only">{{ trans_choice('chat.nav_unread', $unread, ['count' => $unread]) }}</span>
        @endif
        <x-icon.chevron-right class="icon match-row__chevron" />
    </span>
</a>
