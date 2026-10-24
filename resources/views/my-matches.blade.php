@extends('layouts.app')

@section('title', __('profile.matches.title'))

@php
    $me = auth()->user();
    // Unread chat per match, only for chats this user can open (interested players or closed chats get no number)
    $chatIds = \App\Support\Chat::rooms($me)->pluck('id');
    $unread = \App\Support\Chat::unreadByRoom($me, $rooms->getCollection()->pluck('id')->intersect($chatIds)->values());
@endphp

@push('styles')
<style>
    /* "Meciurile mele" on phones (Figma My Bookings). Tokens only. */
    .mm-head { display: flex; align-items: center; justify-content: space-between; gap: var(--space-3); }
    .mm-week { margin-top: var(--space-4); }
    .mm-count { margin-left: var(--space-1); }
    .my-banner {
        display: grid; justify-items: center; gap: var(--space-2); margin-top: var(--space-5); padding: var(--space-5) var(--space-4);
        border: 1.5px dashed var(--separator); border-radius: var(--radius-card); text-align: center;
    }
</style>
@endpush

@section('content')
    <header class="mm-head m-only">
        <h1 class="t-large">{{ __('profile.matches.title') }}</h1>
        <button type="button" class="icon-btn" x-data x-on:click="$dispatch('open-sheet', 'my-calendar')" aria-label="{{ __('profile.matches.calendar') }}"><x-icon.calendar-days /></button>
    </header>
    <h1 class="t-large d-only">{{ __('profile.matches.title') }}</h1>

    {{-- Phones: this week with a dot on my days; a tap opens the full calendar on that day --}}
    <x-week-strip mode="dots" mine class="mm-week m-only"
                  x-on:week-day="$dispatch('open-sheet', 'my-calendar'); $nextTick(() => $dispatch('calendar-open-day', { date: $event.detail.date }))" />

    {{-- Personal calendar: my days ringed, my role, money, equipment, players, quick actions (a sheet on phones) --}}
    <x-sheet name="my-calendar" :title="__('profile.matches.calendar')" inline-desktop>
        <x-calendar :city="$me->city" personal mine class="my-calendar" />
    </x-sheet>

    <nav class="segmented segmented--tabs" aria-label="{{ __('profile.matches.title') }}">
        @foreach (['upcoming', 'past'] as $key)
            <a href="{{ route('my-matches', $key === 'past' ? ['tab' => 'past'] : []) }}" @if ($tab === $key) aria-current="page" @endif>
                <span>{{ __('profile.matches.'.$key) }}</span>@if ($tab === $key && $key === 'upcoming')<span class="m-only mm-count">({{ $rooms->total() }})</span>@endif
            </a>
        @endforeach
    </nav>

    @if ($rooms->isEmpty())
        <x-empty-state :icon="$tab === 'past' ? 'calendar-days' : 'calendar-x'"
                       :title="__('profile.matches.empty_'.$tab.'_title')" :text="__('profile.matches.empty_'.$tab.'_text')">
            <x-button :href="route('rooms.index')" icon="search">{{ __('profile.matches.find') }}</x-button>
        </x-empty-state>
    @else
        {{-- Phones: rows with the date, my role and unread chat --}}
        <div class="match-rows m-only">
            @foreach ($rooms as $room)
                <x-match-row :room="$room" square="date" :role="$room->user_id === $me->id ? 'organizer' : $room->pivot->status" :unread="$unread[$room->id] ?? 0" />
            @endforeach
        </div>

        <div class="match-list d-only">
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

    <section class="my-banner m-only" aria-labelledby="my-banner-title">
        <h2 id="my-banner-title" class="t-headline">{{ __('profile.matches.banner_title') }}</h2>
        <p class="t-footnote t-secondary">{{ __('profile.matches.banner_text') }}</p>
        <x-button variant="secondary" :href="route('rooms.create')" icon="circle-plus">{{ __('profile.matches.banner_action') }}</x-button>
    </section>
@endsection
