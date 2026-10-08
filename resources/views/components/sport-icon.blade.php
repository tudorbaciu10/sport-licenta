{{-- <x-sport-icon :sport="$sport" size="sm|md|lg" />
     Sport glyph on the solid sport color, drawn in its --on-sport color (≥ 3:1 for every sport).
     Decorative: the sport name is always shown next to it. --}}
@props(['sport', 'size' => 'md'])
@php
    $var = $sport->cssVar();
@endphp
<span {{ $attributes->class(['sport-icon', 'sport-icon--'.$size]) }}
      style="--c: var({{ $var }}); --on: var(--on-{{ substr($var, 2) }})" aria-hidden="true">
    <x-dynamic-component :component="$sport->icon()" class="icon" />
</span>
