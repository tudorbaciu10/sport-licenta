@php
    $user = auth()->user();
    $isCreator = $user && $event->user_id === $user->id;
    $isParticipant = $user && $event->hasParticipant($user);
    $badge = match ($event->status) {
        'open' => 'badge--open',
        'full' => 'badge--full',
        default => 'badge--other',
    };
@endphp

<div class="panel">
    <div class="panel__head">
        <div>
            <span class="room-card__sport">{{ $event->sport?->name }}</span>
            <span class="room-card__badge {{ $badge }}">{{ __(ucfirst($event->status)) }}</span>
        </div>
        <button type="button" class="panel__close" data-panel-close aria-label="{{ __('landing.panel_close') }}">&times;</button>
    </div>

    <div class="panel__media">
        <img src="{{ $event->sport?->imageUrl() ?? asset('assets/images/sports/default.svg') }}" alt="{{ $event->sport?->name }}">
    </div>

    <h2 class="panel__title">{{ $event->title }}</h2>

    @isset($flash)
        <div class="panel-flash {{ ($flashType ?? 'status') === 'error' ? 'panel-flash--error' : 'panel-flash--ok' }}">{{ $flash }}</div>
    @endisset

    @if ($event->description)
        <p class="panel__desc">{{ $event->description }}</p>
    @endif

    <dl class="panel__meta">
        <div><dt>{{ __('landing.card_when') }}</dt><dd>{{ $event->start_time->format('D, d M Y · H:i') }}@if ($event->end_time) – {{ $event->end_time->format('H:i') }}@endif</dd></div>
        <div><dt>{{ __('landing.card_where') }}</dt><dd>{{ $event->venue?->name ?? $event->city ?? __('landing.card_tbd') }}</dd></div>
        <div><dt>{{ __('landing.card_organizer') }}</dt><dd>{{ $event->creator?->name }}</dd></div>
        <div><dt>{{ __('landing.card_players') }}</dt><dd>{{ $event->participants->count() }}@if ($event->max_participants)/{{ $event->max_participants }}@endif @if ($event->skill_level) · {{ __('landing.card_skill') }} {{ $event->skill_level }}/5 @endif</dd></div>
    </dl>

    <div class="panel__cta">
        @guest
            <a href="{{ route('login', ['next' => route('landing.room', $event, false)]) }}" class="btn btn--red">{{ __('landing.card_cta') }}</a>
        @else
            @if ($isCreator)
                <p class="panel__note">{{ __('landing.panel_you_organize') }}</p>
            @elseif ($isParticipant)
                <form class="js-panel-form" method="POST" action="{{ route('events.leave', $event) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn--red">{{ __('landing.panel_leave') }}</button>
                </form>
            @else
                <form class="js-panel-form" method="POST" action="{{ route('events.join', $event) }}">
                    @csrf
                    <button type="submit" class="btn btn--primary">{{ __('landing.panel_join') }}</button>
                </form>
            @endif
        @endguest
    </div>

    <div class="panel__section">
        <h3>{{ __('landing.panel_participants') }}</h3>
        @forelse ($event->participants as $participant)
            <div class="panel__participant">{{ $participant->name }}</div>
        @empty
            <p class="panel__muted">{{ __('landing.panel_no_participants') }}</p>
        @endforelse
    </div>
</div>
