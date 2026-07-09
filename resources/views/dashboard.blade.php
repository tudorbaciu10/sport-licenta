<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('events.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('Create event') }}
            </a>
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

        </div>
    </div>
</x-app-layout>
