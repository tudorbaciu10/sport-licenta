<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('events.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('Create event') }}
                </a>
                <a href="{{ route('venues.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
                    {{ __('venues.add') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status') && ! in_array(session('status'), ['profile-updated', 'password-updated']))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Events you created') }}</h3>
                    @forelse ($createdEvents as $event)
                        <x-event-row :event="$event" />
                    @empty
                        <p class="text-gray-500">
                            {{ __('You have not created any events yet.') }}
                            <a href="{{ route('events.create') }}" class="text-indigo-600 hover:underline">{{ __('Create one') }}</a>.
                        </p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Events you joined') }}</h3>
                    @forelse ($joinedEvents as $event)
                        <x-event-row :event="$event" />
                    @empty
                        <p class="text-gray-500">
                            {{ __('You have not joined any events yet.') }}
                            <a href="{{ route('events.index') }}" class="text-indigo-600 hover:underline">{{ __('Browse events') }}</a>.
                        </p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">{{ __('venues.my_heading') }}</h3>
                        <a href="{{ route('venues.mine') }}" class="text-indigo-600 hover:underline text-sm">{{ __('venues.my_heading') }} →</a>
                    </div>
                    @forelse ($myVenues as $venue)
                        <a href="{{ route('venues.show', $venue) }}" class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 -mx-2 px-2 rounded">
                            <div>
                                <div class="font-medium text-gray-900">{{ $venue->name }}</div>
                                <div class="text-sm text-gray-500">{{ $venue->category?->name }} · {{ $venue->city }}</div>
                            </div>
                            @if ($venue->price_per_hour)
                                <span class="text-sm font-semibold text-gray-700">{{ (int) $venue->price_per_hour }} {{ __('venues.card_price') }}</span>
                            @endif
                        </a>
                    @empty
                        <p class="text-gray-500">
                            {{ __('venues.empty') }}
                            <a href="{{ route('venues.create') }}" class="text-indigo-600 hover:underline">{{ __('venues.add') }}</a>.
                        </p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
