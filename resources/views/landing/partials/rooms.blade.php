{{-- Rooms grid contents — rendered on load and re-rendered via AJAX (landing.js) --}}
@forelse ($events as $event)
    @php
        $badge = match ($event->status) {
            'open' => 'badge--open',
            'full' => 'badge--full',
            default => 'badge--other',
        };
    @endphp
    <article class="room-card" id="room-{{ $event->id }}" data-room-id="{{ $event->id }}">
        <div class="room-card__media">
            <img src="{{ $event->sport?->imageUrl() ?? asset('assets/images/sports/default.svg') }}"
                 alt="{{ $event->sport?->name }}" loading="lazy">
        </div>

        <div class="room-card__body">
        <div class="room-card__top">
            <span class="room-card__sport">{{ $event->sport?->name }}</span>
            <span class="room-card__badge {{ $badge }}">{{ ucfirst($event->status) }}</span>
        </div>

        <h3 class="room-card__title">{{ $event->title }}</h3>

        <ul class="room-card__meta">
            <li>
                <b>{{ __('landing.card_when') }}:</b>
                {{ $event->start_time->format('d M Y · H:i') }}
            </li>
            <li>
                <b>{{ __('landing.card_where') }}:</b>
                {{ $event->venue?->name ?? $event->city ?? __('landing.card_tbd') }}
            </li>
            <li>
                <b>{{ __('landing.card_organizer') }}:</b>
                {{ $event->creator?->name }}
                @if ($event->skill_level)
                    · {{ __('landing.card_skill') }} {{ $event->skill_level }}/5
                @endif
            </li>
        </ul>

        <div class="room-card__footer">
            <span class="room-card__players">
                {{ $event->participants_count }}@if ($event->max_participants)/{{ $event->max_participants }}@endif
                <small>{{ __('landing.card_players') }}</small>
            </span>
            <a id="btn-join-room-{{ $event->id }}" class="btn btn--red" href="{{ route('login') }}">{{ __('landing.card_cta') }}</a>
        </div>
        </div>{{-- /.room-card__body --}}
    </article>
@empty
    <div class="rooms-empty" id="rooms-empty">
        {{ __('landing.rooms_empty') }}
    </div>
@endforelse
