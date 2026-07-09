<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $event->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-2xl font-semibold text-gray-900">{{ $event->title }}</div>
                        <div class="text-gray-500">{{ $event->sport?->name }}</div>
                    </div>
                    <span class="inline-block px-3 py-1 text-sm rounded-full
                        {{ $event->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                        {{ ucfirst($event->status) }}
                    </span>
                </div>

                @if ($event->description)
                    <p class="text-gray-700 whitespace-pre-line">{{ $event->description }}</p>
                @endif

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">{{ __('When') }}</dt>
                        <dd class="text-gray-900">
                            {{ $event->start_time->format('D, d M Y H:i') }}
                            @if ($event->end_time) &ndash; {{ $event->end_time->format('H:i') }} @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Where') }}</dt>
                        <dd class="text-gray-900">{{ $event->venue?->name ?? $event->city ?? __('To be decided') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Organizer') }}</dt>
                        <dd class="text-gray-900">{{ $event->creator?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Players') }}</dt>
                        <dd class="text-gray-900">
                            {{ $event->participants->count() }}@if ($event->max_participants)/{{ $event->max_participants }}@endif
                            @if ($event->skill_level) &middot; {{ __('skill') }} {{ $event->skill_level }}/5 @endif
                        </dd>
                    </div>
                </dl>

                @php
                    $isCreator = $event->user_id === auth()->id();
                    $isParticipant = $event->hasParticipant(auth()->user());
                @endphp

                <div class="pt-2 border-t border-gray-100">
                    @if ($isCreator)
                        <p class="text-sm text-gray-500">{{ __('You are the organizer of this event.') }}</p>
                    @elseif ($isParticipant)
                        <form method="POST" action="{{ route('events.leave', $event) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
                                {{ __('Leave event') }}
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('events.join', $event) }}">
                            @csrf
                            <x-primary-button>{{ __('Join event') }}</x-primary-button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Participant list --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-3">{{ __('Participants') }}</h3>
                @forelse ($event->participants as $participant)
                    <div class="py-1 text-gray-800">{{ $participant->name }}</div>
                @empty
                    <p class="text-gray-500">{{ __('No players have joined yet. Be the first!') }}</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
