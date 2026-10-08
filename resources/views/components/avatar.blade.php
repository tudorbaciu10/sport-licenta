{{-- <x-avatar :user="$user" size="sm|md|lg" /> or <x-avatar name="Ion Popescu" />
     Initials on --bg-subtle until profile photos exist (Faza 5). --}}
@props(['user' => null, 'name' => null, 'size' => 'md', 'highlight' => false])
@php
    $name = $name ?? $user?->name ?? '?';
    $initials = mb_strtoupper(collect(preg_split('/\s+/u', trim($name)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->join(''));
@endphp
<span {{ $attributes->class(['avatar', 'avatar--'.$size, 'avatar--highlight' => $highlight]) }} role="img" aria-label="{{ $name }}">{{ $initials }}</span>
