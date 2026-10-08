{{-- <x-sport-card :sport="$sport" :count="12" href="…" />
     The one colourful element of the app: sport tint background, solid icon, name, open matches. --}}
@props(['sport', 'count' => 0, 'href' => null])
<a href="{{ $href ?? route('rooms.index', ['sport' => $sport->slug]) }}"
   {{ $attributes->class('sport-card') }} style="--c: var({{ $sport->cssVar() }})">
    <x-sport-icon :sport="$sport" size="lg" />
    <span class="sport-card__text">
        <span class="t-headline">{{ $sport->label() }}</span>
        <span class="t-footnote sport-card__count">{{ trans_choice('components.sport_card.open_matches', $count, ['count' => $count]) }}</span>
    </span>
</a>
