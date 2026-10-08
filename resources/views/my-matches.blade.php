@extends('layouts.app')

@section('title', __('profile.matches.title'))

@section('content')
    <h1 class="t-large">{{ __('profile.matches.title') }}</h1>

    {{-- Personal calendar: my days ringed, my role, money, equipment, players, quick actions --}}
    <x-calendar :city="auth()->user()->city" personal mine class="my-calendar" />

    <nav class="segmented segmented--tabs" aria-label="{{ __('profile.matches.title') }}">
        @foreach (['upcoming', 'past'] as $key)
            <a href="{{ route('my-matches', $key === 'past' ? ['tab' => 'past'] : []) }}" @if ($tab === $key) aria-current="page" @endif>
                <span>{{ __('profile.matches.'.$key) }}</span>
            </a>
        @endforeach
    </nav>

    @if ($rooms->isEmpty())
        <x-empty-state :icon="$tab === 'past' ? 'calendar-days' : 'calendar-x'"
                       :title="__('profile.matches.empty_'.$tab.'_title')" :text="__('profile.matches.empty_'.$tab.'_text')">
            <x-button :href="route('rooms.index')" icon="search">{{ __('profile.matches.find') }}</x-button>
        </x-empty-state>
    @else
        <div class="match-list">
            @foreach ($rooms as $room)
                @php($role = $room->user_id === auth()->id() ? 'organizer' : $room->pivot->status)
                <div class="my-match">
                    <span @class(['my-match__role', 't-footnote', 'my-match__role--'.$role])>{{ __('profile.matches.role.'.$role) }}</span>
                    <x-match-card :room="$room" />
                </div>
            @endforeach
        </div>

        @if ($rooms->hasPages())
            <nav class="pager-row" aria-label="{{ __('rooms.index.page', ['current' => $rooms->currentPage(), 'last' => $rooms->lastPage()]) }}">
                <span class="t-callout t-secondary">{{ __('rooms.index.page', ['current' => $rooms->currentPage(), 'last' => $rooms->lastPage()]) }}</span>
                <span class="pager-row__buttons">
                    @if ($rooms->previousPageUrl())
                        <x-button variant="secondary" :href="$rooms->previousPageUrl()" icon="arrow-left">{{ __('rooms.index.previous') }}</x-button>
                    @endif
                    @if ($rooms->nextPageUrl())
                        <x-button :href="$rooms->nextPageUrl()" icon-right="arrow-right">{{ __('rooms.index.next') }}</x-button>
                    @endif
                </span>
            </nav>
        @endif
    @endif
@endsection
