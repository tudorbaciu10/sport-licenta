{{-- <x-sport-card :sport="$sport" :count="12" href="…" [compact] />
     compact = one row (icon, name, count) for tight spaces such as the home page next to the calendar.
     The one colourful element of the app: sport tint background, solid icon, name, open matches. --}}
@props(['sport', 'count' => 0, 'href' => null, 'compact' => false])
<a href="{{ $href ?? route('rooms.index', ['sport' => $sport->slug]) }}"
   {{ $attributes->class(['sport-card', 'sport-card--compact' => $compact]) }} style="--c: var({{ $sport->cssVar() }})">
    <x-sport-icon :sport="$sport" :size="$compact ? 'md' : 'lg'" />
    <span class="sport-card__text">
        <span class="t-headline">{{ $sport->label() }}</span>
        <span class="t-footnote sport-card__count">{{ trans_choice($compact ? 'components.sport_card.open_matches_short' : 'components.sport_card.open_matches', $count, ['count' => $count]) }}</span>
    </span>
</a>
