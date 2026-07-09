@props(['event'])

<a href="{{ route('events.show', $event) }}"
   class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 -mx-2 px-2 rounded">
    <div>
        <div class="font-medium text-gray-900">{{ $event->title }}</div>
        <div class="text-sm text-gray-500">
            {{ $event->sport?->name }}
            &middot; {{ $event->start_time->format('D, d M Y H:i') }}
            @if ($event->city)
                &middot; {{ $event->city }}
            @endif
        </div>
    </div>
    <div class="text-sm text-gray-600 text-right">
        <div>{{ $event->participants_count ?? $event->participants()->count() }}@if ($event->max_participants)/{{ $event->max_participants }}@endif {{ __('players') }}</div>
        <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full
            {{ $event->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
            {{ ucfirst($event->status) }}
        </span>
    </div>
</a>
