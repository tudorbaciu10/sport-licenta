<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Player Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if (session('status') === 'profile-updated')
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">
                        {{ __('Profile saved.') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.details.update') }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="bio" :value="__('Bio')" />
                        <textarea id="bio" name="bio" rows="3"
                                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('bio', $profile?->bio) }}</textarea>
                        <x-input-error :messages="$errors->get('bio')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="city" :value="__('City')" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                                          :value="old('city', $profile?->city)" />
                            <x-input-error :messages="$errors->get('city')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="skill_level" :value="__('Overall skill (1–5)')" />
                            <x-text-input id="skill_level" name="skill_level" type="number" min="1" max="5"
                                          class="mt-1 block w-full" :value="old('skill_level', $profile?->skill_level ?? 1)" />
                            <x-input-error :messages="$errors->get('skill_level')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="latitude" :value="__('Latitude (optional)')" />
                            <x-text-input id="latitude" name="latitude" type="number" step="any"
                                          class="mt-1 block w-full" :value="old('latitude', $profile?->latitude)" />
                            <x-input-error :messages="$errors->get('latitude')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="longitude" :value="__('Longitude (optional)')" />
                            <x-text-input id="longitude" name="longitude" type="number" step="any"
                                          class="mt-1 block w-full" :value="old('longitude', $profile?->longitude)" />
                            <x-input-error :messages="$errors->get('longitude')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label :value="__('Sports you play')" />
                        <p class="text-sm text-gray-500 mb-2">{{ __('Tick a sport and set your skill (1–5) for it.') }}</p>
                        <div class="space-y-2">
                            @foreach ($sports as $sport)
                                @php $isSelected = $selectedSkill->has($sport->id); @endphp
                                <div class="flex items-center gap-3">
                                    <label class="inline-flex items-center gap-2 w-48">
                                        <input type="checkbox" name="sports[]" value="{{ $sport->id }}"
                                               @checked($isSelected)
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span>{{ $sport->name }}</span>
                                    </label>
                                    <input type="number" min="1" max="5"
                                           name="sport_skill[{{ $sport->id }}]"
                                           value="{{ old('sport_skill.'.$sport->id, $selectedSkill->get($sport->id, 1)) }}"
                                           class="w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                           aria-label="{{ __('Skill for') }} {{ $sport->name }}">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save profile') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
