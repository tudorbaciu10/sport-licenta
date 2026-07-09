<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Events') }}
            </h2>
            <a href="{{ route('events.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('Create event') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">{{ session('error') }}</div>
            @endif

            {{-- Filter form --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('events.index') }}"
                      class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                    <div>
                        <x-input-label for="sport" :value="__('Sport')" />
                        <select name="sport" id="sport"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All sports') }}</option>
                            @foreach ($sports as $sport)
                                <option value="{{ $sport->id }}" @selected(($filters['sport'] ?? null) == $sport->id)>
                                    {{ $sport->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="city" :value="__('City')" />
                        <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                                      :value="$filters['city'] ?? ''" />
                    </div>
                    <div>
                        <x-input-label for="date" :value="__('From date')" />
                        <x-text-input id="date" name="date" type="date" class="mt-1 block w-full"
                                      :value="$filters['date'] ?? ''" />
                    </div>
                    <div class="flex gap-2">
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                        <a href="{{ route('events.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            {{-- Results --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @forelse ($events as $event)
                    <x-event-row :event="$event" />
                @empty
                    <p class="text-gray-500">{{ __('No upcoming events match your filters.') }}</p>
                @endforelse

                <div class="mt-4">
                    {{ $events->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
