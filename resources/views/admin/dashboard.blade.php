<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin panel') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">{{ session('status') }}</div>
            @endif

            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ([
                    ['label' => 'Users', 'value' => $stats['users']],
                    ['label' => 'Admins', 'value' => $stats['admins']],
                    ['label' => 'Events', 'value' => $stats['events']],
                    ['label' => 'Upcoming', 'value' => $stats['upcomingEvents']],
                    ['label' => 'Sports', 'value' => $stats['sports']],
                    ['label' => 'Facilities', 'value' => $stats['facilities']],
                ] as $stat)
                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <div class="text-3xl font-bold text-gray-900">{{ $stat['value'] }}</div>
                        <div class="text-sm text-gray-500">{{ __($stat['label']) }}</div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Manage sports --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Sports') }}</h3>

                    <form method="POST" action="{{ route('admin.sports.store') }}" class="flex items-end gap-2 mb-4">
                        @csrf
                        <div class="flex-1">
                            <x-input-label for="name" :value="__('New sport')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                          :value="old('name')" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <x-primary-button>{{ __('Add') }}</x-primary-button>
                    </form>

                    <ul class="divide-y divide-gray-100">
                        @foreach ($sports as $sport)
                            <li class="flex items-center justify-between py-2">
                                <span class="text-gray-800">{{ $sport->name }}
                                    <span class="text-gray-400 text-sm">({{ $sport->events_count }} {{ __('events') }})</span>
                                </span>
                                <form method="POST" action="{{ route('admin.sports.destroy', $sport) }}"
                                      onsubmit="return confirm('{{ __('Delete this sport and all its events?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-500 text-sm">{{ __('Delete') }}</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Manage facility categories --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Facility categories') }}</h3>

                    <form method="POST" action="{{ route('admin.venue-categories.store') }}" class="flex items-end gap-2 mb-4">
                        @csrf
                        <div class="flex-1">
                            <x-input-label for="cat_name" :value="__('New category')" />
                            <x-text-input id="cat_name" name="name" type="text" class="mt-1 block w-full"
                                          :value="old('name')" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div class="w-20">
                            <x-input-label for="cat_icon" :value="__('Icon')" />
                            <x-text-input id="cat_icon" name="icon" type="text" class="mt-1 block w-full" :value="old('icon')" placeholder="🏟️" />
                        </div>
                        <x-primary-button>{{ __('Add') }}</x-primary-button>
                    </form>

                    <ul class="divide-y divide-gray-100">
                        @foreach ($venueCategories as $category)
                            <li class="flex items-center justify-between py-2">
                                <span class="text-gray-800">{{ $category->icon }} {{ $category->name }}
                                    <span class="text-gray-400 text-sm">({{ $category->venues_count }})</span>
                                </span>
                                <form method="POST" action="{{ route('admin.venue-categories.destroy', $category) }}"
                                      onsubmit="return confirm('{{ __('Delete this category?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-500 text-sm">{{ __('Delete') }}</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Recent events --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Recent events') }}</h3>
                    <ul class="divide-y divide-gray-100">
                        @forelse ($recentEvents as $event)
                            <li class="py-2">
                                <a href="{{ route('events.show', $event) }}" class="text-gray-900 hover:underline font-medium">{{ $event->title }}</a>
                                <div class="text-sm text-gray-500">
                                    {{ $event->sport?->name }} &middot; {{ __('by') }} {{ $event->creator?->name }}
                                    &middot; {{ $event->start_time->format('d M Y H:i') }}
                                </div>
                            </li>
                        @empty
                            <li class="py-2 text-gray-500">{{ __('No events yet.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
