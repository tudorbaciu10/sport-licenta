{{-- Venues grid contents — rendered on load and re-rendered via AJAX (venues.js) --}}
@forelse ($venues as $venue)
    <article class="venue-card" id="venue-{{ $venue->id }}" data-venue-id="{{ $venue->id }}">
        <div class="venue-card__media">
            <img src="{{ $venue->photoUrl() }}" alt="{{ $venue->name }}" loading="lazy">
            @if ($venue->surface)
                @php $surfaceKey = 'venues.surface_'.$venue->surface; @endphp
                <span class="venue-card__surface">{{ __($surfaceKey) === $surfaceKey ? ucfirst($venue->surface) : __($surfaceKey) }}</span>
            @endif
        </div>

        <div class="venue-card__body">
            <div class="venue-card__top">
                <span class="venue-card__category">{{ $venue->category?->name }}</span>
                <span class="venue-card__inout">{{ $venue->is_indoor ? __('venues.card_indoor') : __('venues.card_outdoor') }}</span>
            </div>

            <h3 class="venue-card__title">{{ $venue->name }}</h3>

            <div class="venue-card__meta">
                📍 {{ $venue->city }}@if ($venue->locality), {{ $venue->locality }}@endif · {{ $venue->country }}
            </div>

            <div class="venue-card__footer">
                <span class="venue-card__price">
                    @if ($venue->price_per_hour)
                        {{ (int) $venue->price_per_hour }} <small>{{ __('venues.card_price') }}</small>
                    @else
                        <small>{{ __('venues.card_price_na') }}</small>
                    @endif
                </span>
                <a id="btn-view-venue-{{ $venue->id }}" class="btn btn--primary" href="{{ route('venues.show', $venue) }}">
                    {{ __('venues.card_view') }}
                </a>
            </div>
        </div>
    </article>
@empty
    <div class="rooms-empty" id="venues-empty">
        {{ __('venues.empty') }}
    </div>
@endforelse
