@extends('layouts.app')

@section('title', __('chat.title'))

@php
    $me = auth()->user();
    $locale = app()->getLocale();
    $when = fn ($room) => \Illuminate\Support\Str::ucfirst($room->match_date_time->locale($locale)->isoFormat('ddd, D MMM · HH:mm'));
    $preview = function ($last) use ($me) {
        if (! $last) {
            return __('chat.no_messages');
        }
        $who = match (true) { $last->user_id === $me->id => __('chat.you'), $last->support_user_id && $last->author->is_support => __('chat.support_team'), default => strtok($last->author->name, ' ') };

        return $who.': '.\Illuminate\Support\Str::limit($last->body, 60);
    };
    $stamp = fn ($last) => $last ? ($last->created_at->isToday() ? $last->created_at->format('H:i') : $last->created_at->locale($locale)->isoFormat('D MMM')) : null;
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ \App\Support\Asset::url('assets/css/chat.css') }}">
@endpush

@section('content')
<div @class(['chat', 'chat--open' => $conversation])>
    {{-- Conversation list --}}
    <aside class="chat-list" aria-labelledby="chat-title">
        <h1 id="chat-title" class="t-title1 chat-list__title">{{ __('chat.title') }}</h1>

        <h2 class="chat-list__head">{{ __('chat.matches') }}</h2>
        @if ($rooms->isEmpty())
            <p class="chat-list__empty t-footnote t-secondary">{{ __('chat.no_matches') }} <a href="{{ route('rooms.index') }}">{{ __('chat.find_match') }}</a></p>
        @else
            <ul class="chat-list__items" role="list">
                @foreach ($rooms as $item)
                    <li>
                        <a href="{{ $item['conversation']->url() }}" class="chat-item" @if ($item['active']) aria-current="page" @endif>
                            <x-sport-icon :sport="$item['room']->sport" />
                            <span class="chat-item__text">
                                <span class="chat-item__top">
                                    <span class="chat-item__title">{{ $item['title'] }}</span>
                                    @if ($stamp($item['last']))<time class="chat-item__time">{{ $stamp($item['last']) }}</time>@endif
                                </span>
                                <span class="chat-item__preview">{{ $preview($item['last']) }}</span>
                            </span>
                            @if ($item['unread'])
                                <span class="count-badge">{{ min(99, $item['unread']) }}<span class="sr-only"> {{ trans_choice('chat.unread', $item['unread'], ['count' => $item['unread']]) }}</span></span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <h2 class="chat-list__head">{{ $me->is_support ? __('chat.support_threads') : __('chat.support') }}</h2>
        @if ($threads->isEmpty())
            <p class="chat-list__empty t-footnote t-secondary">{{ __('chat.no_messages') }}</p>
        @else
            <ul class="chat-list__items" role="list">
                @foreach ($threads as $item)
                    <li>
                        <a href="{{ $item['conversation']->url() }}" class="chat-item" @if ($item['active']) aria-current="page" @endif>
                            @if ($me->is_support)
                                <x-avatar :user="$item['owner']" />
                            @else
                                <span class="chat-item__support" aria-hidden="true"><x-icon.life-buoy /></span>
                            @endif
                            <span class="chat-item__text">
                                <span class="chat-item__top">
                                    <span class="chat-item__title">{{ $item['title'] }}</span>
                                    @if ($stamp($item['last']))<time class="chat-item__time">{{ $stamp($item['last']) }}</time>@endif
                                </span>
                                <span class="chat-item__preview">{{ $item['last'] ? $preview($item['last']) : __('chat.support_team') }}</span>
                            </span>
                            @if ($item['unread'])
                                <span class="count-badge">{{ min(99, $item['unread']) }}<span class="sr-only"> {{ trans_choice('chat.unread', $item['unread'], ['count' => $item['unread']]) }}</span></span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </aside>

    {{-- Open conversation --}}
    <section class="chat-pane" aria-labelledby="{{ $conversation ? 'conv-title' : 'pick-title' }}">
        @if (! $conversation)
            <x-empty-state icon="message-circle" :title="__('chat.pick_title')" :text="__('chat.pick_text')" id="pick-title">
                <x-button :href="route('rooms.index')" icon="search" variant="secondary">{{ __('chat.find_match') }}</x-button>
            </x-empty-state>
        @else
            @php
                $isRoom = (bool) $room;
                $owner = $conversation->owner;
                $config = [
                    'me' => $me->id,
                    'messages' => $messages,
                    'hasOlder' => $hasOlder,
                    'channel' => $conversation->channel(),
                    'messagesUrl' => route('chat.messages', [$conversation->kind(), $conversation->id()]),
                    'sendUrl' => route('chat.send', [$conversation->kind(), $conversation->id()]),
                    'readUrl' => route('chat.read', [$conversation->kind(), $conversation->id()]),
                    'max' => \App\Models\Message::MAX_LENGTH,
                    'locale' => $locale,
                    'today' => today()->toDateString(),
                    'yesterday' => today()->subDay()->toDateString(),
                    'reverb' => config('broadcasting.default') === 'reverb' ? [
                        'key' => config('broadcasting.connections.reverb.key'),
                        'host' => config('broadcasting.connections.reverb.options.host'),
                        'port' => (int) config('broadcasting.connections.reverb.options.port'),
                        'scheme' => config('broadcasting.connections.reverb.options.scheme'),
                    ] : null,
                    't' => [
                        'you' => __('chat.you'), 'today' => __('chat.today'), 'yesterday' => __('chat.yesterday'),
                        'tooLong' => __('chat.too_long', ['max' => \App\Models\Message::MAX_LENGTH]),
                        'newMessage' => __('chat.new_message', ['name' => ':name']), 'support' => __('chat.support_team'),
                    ],
                ];
            @endphp
            <div class="conv" x-data="chatRoom(@js($config))">
                <header class="conv__head">
                    <a href="{{ route('chat.index') }}" class="conv__back" aria-label="{{ __('chat.back') }}"><x-icon.arrow-left /></a>
                    @if ($isRoom)
                        <x-sport-icon :sport="$room->sport" />
                        <div class="conv__title">
                            <h2 id="conv-title" class="t-headline">{{ $room->title }}</h2>
                            <p class="t-footnote t-secondary">{{ $when($room) }} · {{ $room->location_name }}</p>
                        </div>
                        <button type="button" class="conv__action" x-on:click="$refs.people.showModal()">
                            <x-icon.users /><span>{{ $room->players->count() }}</span>
                            <span class="sr-only">{{ __('chat.participants') }}</span>
                        </button>
                        <a href="{{ route('rooms.show', $room) }}" class="conv__action" aria-label="{{ __('chat.open_match') }}"><x-icon.info /></a>
                    @else
                        @if ($me->is_support && $owner->id !== $me->id)
                            <x-avatar :user="$owner" />
                        @else
                            <span class="chat-item__support" aria-hidden="true"><x-icon.life-buoy /></span>
                        @endif
                        <div class="conv__title">
                            <h2 id="conv-title" class="t-headline">{{ $me->is_support && $owner->id !== $me->id ? $owner->name : __('chat.support') }}</h2>
                            <p class="t-footnote t-secondary">{{ $me->is_support && $owner->id !== $me->id ? $owner->email : __('chat.support_team') }}</p>
                        </div>
                    @endif
                </header>

                <div class="conv__log" x-ref="log" x-on:scroll.passive="onScroll()">
                    <div class="conv__older" x-show="hasOlder" x-cloak>
                        <button type="button" class="btn btn--ghost btn--sm" x-on:click="loadOlder()" :disabled="loadingOlder">{{ __('chat.older') }}</button>
                    </div>

                    <div class="conv__empty" x-show="!messages.length" x-cloak>
                        <span class="empty-state__icon" aria-hidden="true"><x-icon.message-circle /></span>
                        <p class="t-headline">{{ __('chat.no_messages') }}</p>
                        <p class="t-callout t-secondary">{{ $isRoom ? __('chat.empty_room') : __('chat.empty_support') }}</p>
                    </div>

                    <ol class="conv__messages" role="log" aria-label="{{ __('chat.conversations') }}" aria-live="polite">
                        <template x-for="row in rows" :key="row.key">
                            <li :class="row.type === 'day' ? 'conv__day' : { msg: true, 'msg--mine': row.mine, 'msg--first': row.first, 'msg--support': row.support }">
                                <template x-if="row.type === 'day'"><span x-text="row.label"></span></template>
                                <template x-if="row.type === 'msg'">
                                    <div class="msg__row">
                                        <span class="msg__avatar" aria-hidden="true">
                                            <template x-if="row.first && !row.mine">
                                                <span>
                                                    <template x-if="row.avatar"><img :src="row.avatar" alt="" class="avatar avatar--sm avatar--photo"></template>
                                                    <template x-if="!row.avatar"><span class="avatar avatar--sm" x-text="row.initials"></span></template>
                                                </span>
                                            </template>
                                        </span>
                                        <div class="msg__body">
                                            <span class="msg__author" x-show="row.first && !row.mine" x-text="row.support ? t.support : row.author"></span>
                                            <p class="msg__bubble" x-text="row.body"></p>
                                            <span class="msg__meta">
                                                <span class="sr-only" x-text="row.mine ? t.you : row.author"></span>
                                                <time x-text="row.time"></time>
                                                <template x-if="row.pending && !row.failed"><span>· {{ __('chat.sending') }}</span></template>
                                                <template x-if="row.failed">
                                                    <span class="msg__failed">· {{ __('chat.failed') }}
                                                        <button type="button" class="msg__retry" x-on:click="retry(row.key)">{{ __('chat.retry') }}</button>
                                                    </span>
                                                </template>
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            </li>
                        </template>
                    </ol>
                </div>

                <p class="conv__offline t-footnote t-secondary" x-show="offline" x-cloak role="status">{{ __('chat.offline') }}</p>

                <form class="composer" x-on:submit.prevent="send()">
                    <label for="chat-input" class="sr-only">{{ __('chat.message_label') }}</label>
                    <textarea id="chat-input" class="input composer__input" rows="1" x-ref="input" x-model="text"
                              maxlength="{{ \App\Models\Message::MAX_LENGTH }}" placeholder="{{ __('chat.placeholder') }}"
                              aria-describedby="chat-hint" enterkeyhint="send"
                              x-on:keydown.enter="onEnter($event)" x-on:input="grow()"></textarea>
                    <span id="chat-hint" class="sr-only">{{ __('chat.hint') }}</span>
                    <button type="submit" class="composer__send" :disabled="!text.trim()" aria-label="{{ __('chat.send') }}"><x-icon.send /></button>
                </form>
                <p class="composer__error t-footnote" x-show="error" x-text="error" x-cloak role="alert"></p>

                @if ($isRoom)
                    {{-- Players, with the social networks they chose to share (only people in this match see them) --}}
                    <dialog class="people" x-ref="people" aria-labelledby="people-title" x-on:click.self="$el.close()">
                        <div class="people__inner">
                            <header class="people__head">
                                <h2 id="people-title" class="t-title2">{{ __('chat.participants') }}</h2>
                                <button type="button" class="conv__action" x-on:click="$refs.people.close()" aria-label="{{ __('chat.close') }}"><x-icon.x /></button>
                            </header>
                            <p class="t-footnote t-secondary">{{ trans_choice('chat.participants_count', $room->players->count(), ['count' => $room->players->count()]) }}</p>
                            <ul class="people__list" role="list">
                                @foreach ($room->players->sortByDesc(fn ($p) => $p->id === $room->user_id) as $player)
                                    @php($level = $player->sports->firstWhere('id', $room->sport_id)?->pivot->level)
                                    <li class="person">
                                        <x-avatar :user="$player" :highlight="$player->id === $room->user_id" />
                                        <div class="person__text">
                                            <p class="t-headline">{{ $player->name }}@if ($player->id === $me->id) <span class="t-secondary">({{ __('chat.you') }})</span>@endif</p>
                                            <p class="t-footnote t-secondary">
                                                @if ($player->id === $room->user_id){{ __('chat.organizer') }}@if ($level) · @endif @endif
                                                @if ($level){{ __('profile.levels.'.$level) }}@endif
                                            </p>
                                            @if ($links = $player->socialLinks())
                                                <ul class="socials" role="list">
                                                    @foreach ($links as $network => $href)
                                                        <li><a href="{{ $href }}" class="social" target="_blank" rel="noopener noreferrer">{{ __('profile.social.'.$network) }} <x-icon.external-link /></a></li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </dialog>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection

@if ($conversation)
    @push('scripts')
        @if (config('broadcasting.default') === 'reverb')
            <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/laravel-echo@2.5.0/dist/echo.iife.js"></script>
        @endif
        <script src="{{ \App\Support\Asset::url('assets/js/chat.js') }}"></script>
    @endpush
@endif
