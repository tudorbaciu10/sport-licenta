{{-- <x-chip :active="true" href="?day=today" icon="…">Astăzi</x-chip>
     A link (filters are GET links) or, without href, a toggle button with aria-pressed.
     Looks 36px tall; the hit area is extended to 44px. --}}
@props(['active' => false, 'href' => null, 'icon' => null, 'sport' => null])
@php
    $style = $sport ? '--c: var('.$sport->cssVar().')' : null;
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class(['chip', 'chip--active' => $active]) }} @if ($style) style="{{ $style }}" @endif
       @if ($active) aria-current="true" @endif>
        @if ($sport)<span class="chip__dot" aria-hidden="true"></span>@endif
        @if ($icon)<x-dynamic-component :component="'icon.'.$icon" class="icon chip__icon" />@endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="button" {{ $attributes->class(['chip', 'chip--active' => $active]) }} @if ($style) style="{{ $style }}" @endif
            aria-pressed="{{ $active ? 'true' : 'false' }}">
        @if ($sport)<span class="chip__dot" aria-hidden="true"></span>@endif
        @if ($icon)<x-dynamic-component :component="'icon.'.$icon" class="icon chip__icon" />@endif
        <span>{{ $slot }}</span>
    </button>
@endif
