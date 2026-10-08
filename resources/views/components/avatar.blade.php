{{-- <x-avatar :user="$user" size="sm|md|lg|xl" /> or <x-avatar name="Ion Popescu" />
     Profile photo when the user has one, otherwise initials on --bg-subtle. --}}
@props(['user' => null, 'name' => null, 'size' => 'md', 'highlight' => false])
@php
    $name = $name ?? $user?->name ?? '?';
    $photo = $user?->avatarUrl();
    $initials = mb_strtoupper(collect(preg_split('/\s+/u', trim($name)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->join(''));
@endphp
@if ($photo)
    <img src="{{ $photo }}" alt="{{ $name }}" {{ $attributes->class(['avatar', 'avatar--'.$size, 'avatar--photo']) }} loading="lazy" decoding="async">
@else
    <span {{ $attributes->class(['avatar', 'avatar--'.$size, 'avatar--highlight' => $highlight]) }} role="img" aria-label="{{ $name }}">{{ $initials }}</span>
@endif
