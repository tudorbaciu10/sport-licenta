{{-- <x-badge status="open|almost|full" />
     Always text + dot, never colour alone. The dot uses the status colour; text stays --text. --}}
@props(['status' => 'open'])
<span {{ $attributes->class(['badge', 'badge--'.$status]) }}>
    <span class="badge__dot" aria-hidden="true"></span>{{ $slot->isEmpty() ? __('components.badge.'.$status) : $slot }}
</span>
