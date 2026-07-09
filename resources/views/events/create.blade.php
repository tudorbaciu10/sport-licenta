<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('events.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                                      :value="old('title')" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="sport_id" :value="__('Sport')" />
                            <select name="sport_id" id="sport_id" required
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('Select a sport') }}</option>
                                @foreach ($sports as $sport)
                                    <option value="{{ $sport->id }}" @selected(old('sport_id') == $sport->id)>{{ $sport->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('sport_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="venue_id" :value="__('Venue (optional)')" />
                            <select name="venue_id" id="venue_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('No venue') }}</option>
                                @foreach ($venues as $venue)
                                    <option value="{{ $venue->id }}" @selected(old('venue_id') == $venue->id)>{{ $venue->name }}{{ $venue->city ? ' — '.$venue->city : '' }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('venue_id')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="3"
                                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="start_time" :value="__('Starts at')" />
                            <x-text-input id="start_time" name="start_time" type="datetime-local" class="mt-1 block w-full"
                                          :value="old('start_time')" required />
                            <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="end_time" :value="__('Ends at (optional)')" />
                            <x-text-input id="end_time" name="end_time" type="datetime-local" class="mt-1 block w-full"
                                          :value="old('end_time')" />
                            <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="city" :value="__('City')" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                                          :value="old('city')" />
                            <x-input-error :messages="$errors->get('city')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="max_participants" :value="__('Max players')" />
                            <x-text-input id="max_participants" name="max_participants" type="number" min="2" max="1000"
                                          class="mt-1 block w-full" :value="old('max_participants')" />
                            <x-input-error :messages="$errors->get('max_participants')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="skill_level" :value="__('Skill level (1–5)')" />
                            <x-text-input id="skill_level" name="skill_level" type="number" min="1" max="5"
                                          class="mt-1 block w-full" :value="old('skill_level')" />
                            <x-input-error :messages="$errors->get('skill_level')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Create event') }}</x-primary-button>
                        <a href="{{ route('events.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
