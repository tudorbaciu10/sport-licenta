{{-- <x-button variant="primary|secondary|ghost" size="sm" block icon="search" href="…">Text</x-button>
     Renders <a> when href is set, otherwise <button>. Always ≥ 44px tall. --}}
@props([
    'variant' => 'primary',
    'size' => null,
    'block' => false,
    'href' => null,
    'icon' => null,
    'iconRight' => null,
    'type' => 'button',
    'disabled' => false,
])
@php
    $classes = ['btn', 'btn--'.$variant, 'btn--sm' => $size === 'sm', 'btn--block' => $block];
@endphp
@if ($href)
    <a href="{{ $disabled ? '#' : $href }}" {{ $attributes->class($classes) }} @if ($disabled) aria-disabled="true" tabindex="-1" @endif>
        @if ($icon)<x-dynamic-component :component="'icon.'.$icon" class="icon icon--sm" />@endif
        {{ $slot }}
        @if ($iconRight)<x-dynamic-component :component="'icon.'.$iconRight" class="icon icon--sm" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }} @disabled($disabled)>
        @if ($icon)<x-dynamic-component :component="'icon.'.$icon" class="icon icon--sm" />@endif
        {{ $slot }}
        @if ($iconRight)<x-dynamic-component :component="'icon.'.$iconRight" class="icon icon--sm" />@endif
    </button>
@endif
