@php($left = $room->spotsLeft())
<a href="{{ route('rooms.show', $room) }}" class="room" style="--c: {{ $room->sport->color }}">
    <div class="room-top">
        <span class="when">{{ $room->match_date_time->format('H:i') }}</span>
        <span class="day">{{ $room->match_date_time->locale('ro')->isoFormat('ddd, D MMM') }}</span>
    </div>
    <div>
        <span class="tag">{{ $room->sport->name }}</span>
        <h3>{{ $room->title }}</h3>
        <span class="muted">{{ $room->location_name }}, {{ $room->city->name }}@if ($room->venueLabel()) · {{ $room->venueLabel() }}@endif</span>
    </div>
    <span class="dots" aria-label="{{ $room->current_players_count }} din {{ $room->max_players }} locuri ocupate">
        @for ($i = 0; $i < $room->max_players; $i++)
            <i class="{{ $i < $room->current_players_count ? '' : 'o' }}"></i>
        @endfor
    </span>
    <div class="room-foot">
        <span>Organizează {{ $room->creator->name }}</span>
        @if ($left === 0)
            <span class="pill full">Complet</span>
        @else
            <span class="pill">{{ $left }} {{ $left === 1 ? 'loc liber' : 'locuri libere' }}</span>
        @endif
    </div>
</a>
