@extends('layouts.shell')

@section('title', $room->title)

@php
    $me = auth()->user();
    $status = $myStatus?->value;              // 'joined' | 'interested' | null
    $isCreator = $me && $me->id === $room->user_id;
    $players = $room->participants->where('pivot.status', 'joined')
        ->sortBy(fn ($u) => $u->id === $room->user_id ? 0 : 1)->values();
    $interested = $room->participants->where('pivot.status', 'interested')->values();
    $left = $room->spotsLeft();
    $joinable = $room->isJoinable() && $left > 0;
    $when = $room->match_date_time->locale(app()->getLocale());
    $pct = $room->max_players ? min(100, round($room->current_players_count / $room->max_players * 100)) : 0;
    $shareText = __('match.show.share_text', ['title' => $room->title, 'when' => $when->isoFormat('dddd, D MMM, HH:mm')]);
@endphp

@push('styles')
<style>
    /* /rooms/{id} only. Tokens only. */
    .back-link { display: inline-flex; align-items: center; gap: var(--space-2); min-height: var(--tap); font-size: var(--fs-callout); font-weight: var(--fw-medium); }
    .back-link .icon { width: 18px; height: 18px; }

    .match-detail { display: grid; gap: var(--space-6); margin-top: var(--space-2); padding-bottom: 140px; }   /* room for the action bar */
    .match-hero { display: grid; gap: var(--space-3); padding-bottom: var(--space-5); border-bottom: 1px solid var(--separator); }
    .match-hero__sport { display: flex; align-items: center; gap: var(--space-3); }
    .match-hero__by { display: flex; align-items: center; gap: var(--space-2); color: var(--text-2); }

    .info-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: var(--space-3); }
    .info { display: grid; gap: var(--space-1); align-content: start; padding: var(--space-4); border-radius: var(--radius-card); background: var(--bg-subtle); }
    .info--wide { grid-column: 1 / -1; }
    .info dt { display: flex; align-items: center; gap: var(--space-2); color: var(--text-2); font-size: var(--fs-footnote); line-height: var(--lh-footnote); }
    .info dt .icon { width: 16px; height: 16px; }
    .info dd { margin: 0; font-weight: var(--fw-semibold); }
    .info dd small { display: block; font-weight: var(--fw-regular); color: var(--text-2); font-size: var(--fs-footnote); line-height: var(--lh-footnote); }
    .info .progress { margin-top: var(--space-2); background: var(--bg); }

    .detail-section { display: grid; gap: var(--space-3); }
    .note { padding: var(--space-4); border-left: 3px solid var(--c); background: var(--bg-subtle); border-radius: 0 var(--radius-control) var(--radius-control) 0; white-space: pre-line; }
    .rule-list { display: grid; gap: var(--space-2); }
    .rule-list li { display: flex; gap: var(--space-3); align-items: flex-start; }
    .rule-list .icon { width: 20px; height: 20px; color: var(--text-2); margin-top: 2px; }
    .people { display: grid; gap: var(--space-1); }
    .people li { display: flex; align-items: center; gap: var(--space-3); min-height: var(--tap); }
    .people__tag { margin-left: auto; }

    .action-bar {
        position: fixed; left: 0; right: 0; bottom: calc(61px + env(safe-area-inset-bottom)); z-index: 35;   /* sits on the bottom nav */
        display: grid; gap: var(--space-2); padding: var(--space-3) var(--gutter);
        background: color-mix(in srgb, var(--bg) 92%, transparent);
        backdrop-filter: saturate(180%) blur(20px); -webkit-backdrop-filter: saturate(180%) blur(20px);
        border-top: 1px solid var(--separator);
    }
    .action-bar__status { display: flex; align-items: center; gap: var(--space-2); font-size: var(--fs-footnote); line-height: var(--lh-footnote); color: var(--text-2); }
    .action-bar__status .icon { width: 16px; height: 16px; }
    .action-bar__row { display: flex; gap: var(--space-2); }
    .action-bar__row > form, .action-bar__row > .btn { flex: 1; }
    .action-bar__row .btn { width: 100%; }
    .action-bar__share { flex: 0 0 var(--tap) !important; padding: 0; }
    .toast { position: fixed; left: 50%; bottom: calc(180px + env(safe-area-inset-bottom)); transform: translateX(-50%); z-index: 50;
             padding: var(--space-2) var(--space-4); border-radius: var(--radius-pill); background: var(--text); color: var(--bg); font-size: var(--fs-callout); }

    @media (min-width: 1024px) {
        .match-detail { grid-template-columns: minmax(0, 1fr) 340px; align-items: start; padding-bottom: 0; }
        .match-detail__main { display: grid; gap: var(--space-6); }
        .info-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .action-bar {
            position: sticky; top: 88px; bottom: auto; padding: var(--space-5);
            border: 1px solid var(--separator); border-radius: var(--radius-card); background: var(--bg); backdrop-filter: none;
        }
        .action-bar__row { flex-direction: column; }
        .action-bar__share { flex: 1 !important; padding: 0 var(--space-5); }
        .action-bar__share span.sr-only { position: static; width: auto; height: auto; margin: 0; overflow: visible; clip: auto; }
        .toast { bottom: var(--space-6); }
    }
</style>
@endpush

@section('content')
    <a class="back-link" href="{{ route('rooms.index', ['sport' => $room->sport->slug, 'city' => $room->city->slug]) }}">
        <x-icon.arrow-left /> {{ __('match.show.back') }}
    </a>

    <div class="match-detail" style="--c: var({{ $room->sport->cssVar() }})">
        <article class="match-detail__main">
            <header class="match-hero">
                <div class="match-hero__sport">
                    <x-sport-icon :sport="$room->sport" size="lg" />
                    <div>
                        <p class="t-headline">{{ $room->sport->label() }}</p>
                        <x-badge :status="$room->availability()" />
                    </div>
                </div>
                <h1 class="t-large">{{ $room->title }}</h1>
                <p class="match-hero__by t-callout">
                    <x-avatar :user="$room->creator" size="sm" />
                    {{ __('match.show.organized_by', ['name' => $room->creator->name]) }}
                </p>
            </header>

            <dl class="info-grid">
                <div class="info">
                    <dt><x-icon.clock /> {{ __('match.show.when') }}</dt>
                    <dd class="tabular">
                        {{ $when->format('H:i') }}
                        <small>{{ \Illuminate\Support\Str::ucfirst($when->isoFormat('dddd, D MMMM')) }}</small>
                    </dd>
                </div>
                <div class="info">
                    <dt><x-icon.map-pin /> {{ __('match.show.where') }}</dt>
                    <dd>{{ $room->location_name }}<small>{{ $room->city->label() }}</small></dd>
                </div>
                <div class="info">
                    <dt><x-icon.house /> {{ __('match.show.venue') }}</dt>
                    <dd>{{ $room->venue_type ? __('rooms.index.'.$room->venue_type) : __('match.show.venue_any') }}</dd>
                </div>
                <div class="info">
                    <dt><x-icon.banknote /> {{ __('match.show.price') }}</dt>
                    <dd>
                        {{ $room->price ? __('match.price.per_player', ['price' => $room->price]) : __('match.price.free') }}
                        @if ($room->price && $room->price_collector)<small>{{ __('match.price.collector.'.$room->price_collector) }}</small>@endif
                    </dd>
                </div>
                @if ($room->equipment_by)
                    <div class="info">
                        <dt><x-icon.shirt /> {{ __('match.show.equipment') }}</dt>
                        <dd>{{ __('match.equipment.'.$room->equipment_by) }}</dd>
                    </div>
                @endif
                <div @class(['info', 'info--wide' => ! $room->equipment_by])>
                    <dt><x-icon.users /> {{ __('match.show.spots') }}</dt>
                    <dd class="tabular">
                        {{ __('components.match.taken', ['taken' => $room->current_players_count, 'max' => $room->max_players]) }}
                        <small>{{ trans_choice('components.match.spots_left', $left, ['count' => $left]) }}</small>
                        <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $room->max_players }}"
                             aria-valuenow="{{ $room->current_players_count }}"
                             aria-label="{{ __('components.match.players', ['taken' => $room->current_players_count, 'max' => $room->max_players]) }}">
                            <span style="width: {{ $pct }}%"></span>
                        </div>
                    </dd>
                </div>
            </dl>

            @if ($room->description)
                <section class="detail-section" aria-labelledby="note-title">
                    <h2 id="note-title" class="t-title2">{{ __('match.show.note') }}</h2>
                    <p class="note measure">{{ $room->description }}</p>
                </section>
            @endif

            @if (! empty($room->rules))
                <section class="detail-section" aria-labelledby="rules-title">
                    <h2 id="rules-title" class="t-title2">{{ __('match.show.rules') }}</h2>
                    <ul class="rule-list" role="list">
                        @foreach ($room->rules as $rule)
                            <li><x-icon.circle-check /> <span>{{ $rule }}</span></li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="detail-section" aria-labelledby="players-title">
                <h2 id="players-title" class="t-title2">{{ __('match.show.players') }} <span class="t-secondary tabular">{{ $players->count() }}</span></h2>
                @if ($players->isEmpty())
                    <p class="t-callout t-secondary">{{ __('match.show.no_players') }}</p>
                @else
                    <ul class="people" role="list">
                        @foreach ($players as $user)
                            <li>
                                <x-avatar :user="$user" :highlight="$user->id === $room->user_id" />
                                <span>{{ $user->name }}</span>
                                @if ($user->id === $room->user_id)
                                    <span class="people__tag t-footnote t-secondary">{{ __('match.show.organizer') }}</span>
                                @elseif ($me && $user->id === $me->id)
                                    <span class="people__tag t-footnote t-secondary">{{ __('match.show.you') }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            @if ($interested->isNotEmpty())
                <section class="detail-section" aria-labelledby="interested-title">
                    <h2 id="interested-title" class="t-title2">{{ __('match.show.interested') }} <span class="t-secondary tabular">{{ $interested->count() }}</span></h2>
                    <ul class="people" role="list">
                        @foreach ($interested as $user)
                            <li><x-avatar :user="$user" size="sm" /> <span class="t-secondary">{{ $user->name }}</span></li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </article>

        {{-- Main action: fixed above the tab bar on phones, sticky side card on laptops --}}
        <aside class="action-bar" aria-label="{{ __('match.show.join') }}"
               x-data="{
                   copied: false,
                   async share() {
                       const data = { title: @js($room->title), text: @js($shareText), url: @js(route('rooms.show', $room)) };
                       if (navigator.share) { try { await navigator.share(data); } catch (e) {} return; }
                       await navigator.clipboard?.writeText(data.url);
                       this.copied = true; setTimeout(() => this.copied = false, 2000);
                   }
               }">
            <p class="action-bar__status">
                @if ($isCreator)
                    <x-icon.info /> {{ __('match.show.you_organize') }}
                @elseif ($status === 'joined')
                    <x-icon.circle-check /> {{ __('match.show.you_play') }}
                @else
                    <x-icon.users /> {{ trans_choice('components.match.spots_left', $left, ['count' => $left]) }}
                @endif
            </p>

            <div class="action-bar__row">
                @guest
                    <x-button :href="route('login')" block>{{ __('match.show.join_guest') }}</x-button>
                @else
                    @if ($status === 'joined')
                        @unless ($isCreator)
                            <form method="POST" action="{{ route('rooms.leave', $room) }}">
                                @csrf @method('DELETE')
                                <x-button variant="secondary" type="submit" block>{{ __('match.show.leave') }}</x-button>
                            </form>
                        @endunless
                    @else
                        <form method="POST" action="{{ route('rooms.join', $room) }}">
                            @csrf
                            <x-button type="submit" block :disabled="! $joinable">
                                {{ $joinable ? __('match.show.join') : ($left === 0 ? __('match.show.full') : __('match.show.not_joinable')) }}
                            </x-button>
                        </form>
                        @if ($status === 'interested')
                            <form method="POST" action="{{ route('rooms.leave', $room) }}">
                                @csrf @method('DELETE')
                                <x-button variant="secondary" type="submit" block>{{ __('match.show.uninterest') }}</x-button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('rooms.interest', $room) }}">
                                @csrf
                                <x-button variant="secondary" type="submit" block>{{ __('match.show.interest') }}</x-button>
                            </form>
                        @endif
                    @endif
                @endguest

                @if ($isCreator)
                    {{-- The organiser's job here is inviting people: full-width share --}}
                    <x-button icon="share-2" block x-on:click="share()">{{ __('match.show.share') }}</x-button>
                @else
                    <button type="button" class="btn btn--secondary action-bar__share" x-on:click="share()">
                        <x-icon.share-2 class="icon icon--sm" /><span class="sr-only">{{ __('match.show.share') }}</span>
                    </button>
                @endif
            </div>

            <p class="toast" role="status" x-show="copied" x-cloak x-transition.opacity>{{ __('match.show.copied') }}</p>
        </aside>
    </div>
@endsection
