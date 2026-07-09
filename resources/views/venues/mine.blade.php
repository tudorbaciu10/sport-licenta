<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('venues.my_heading') }}
            </h2>
            <a href="{{ route('venues.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('venues.add') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @forelse ($venues as $venue)
                    <div class="flex items-center justify-between gap-4 py-3 border-b border-gray-100 last:border-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ $venue->photoUrl() }}" alt="" class="h-12 w-20 object-cover rounded-md shrink-0">
                            <div class="min-w-0">
                                <a href="{{ route('venues.show', $venue) }}" class="font-medium text-gray-900 hover:underline">{{ $venue->name }}</a>
                                <div class="text-sm text-gray-500 truncate">
                                    {{ $venue->category?->name }} · {{ $venue->city }}
                                    @if ($venue->price_per_hour) · {{ (int) $venue->price_per_hour }} {{ __('venues.card_price') }} @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ route('venues.edit', $venue) }}" class="text-indigo-600 hover:underline text-sm">{{ __('venues.edit_heading') }}</a>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">
                        {{ __('venues.empty') }}
                        <a href="{{ route('venues.create') }}" class="text-indigo-600 hover:underline">{{ __('venues.add') }}</a>.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
