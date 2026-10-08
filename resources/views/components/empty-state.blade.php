{{-- <x-empty-state icon="calendar-x" :title="…" :text="…"> <x-button …>Creează primul</x-button> </x-empty-state>
     An empty list is an invitation to act: always pass an action in the slot. --}}
@props(['icon' => 'calendar-x', 'title', 'text' => null])
<div {{ $attributes->class('empty-state') }}>
    <span class="empty-state__icon" aria-hidden="true"><x-dynamic-component :component="'icon.'.$icon" class="icon" /></span>
    <h2 class="t-title2">{{ $title }}</h2>
    @if ($text)
        <p class="t-callout t-secondary measure">{{ $text }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="empty-state__action">{{ $slot }}</div>
    @endif
</div>
