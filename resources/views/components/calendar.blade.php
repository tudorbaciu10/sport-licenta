{{-- <x-calendar :city="$city" />                  public calendar (home)
     <x-calendar :city="$city" personal mine />    personal calendar ("Meciurile mele")
     Logic: public/assets/js/calendar.js · Look: public/assets/css/calendar.css · Data: CalendarController --}}
@props(['city' => null, 'personal' => false, 'mine' => false])
@php
    $sports = \App\Models\Sport::orderBy('name')->get();
    $config = [
        'today' => today()->toDateString(),
        'locale' => app()->getLocale(),
        'city' => $city?->slug,
        'personal' => (bool) $personal,
        'mine' => (bool) $mine,
        'daysUrl' => route('calendar.days'),
        'dayUrl' => route('calendar.day'),
        'createUrl' => route('rooms.create'),
        'roomsUrl' => route('rooms.index'),
        'csrf' => csrf_token(),
        'sportVars' => $sports->mapWithKeys(fn ($s) => [$s->slug => $s->cssVar()]),
        'sportNames' => $sports->mapWithKeys(fn ($s) => [$s->slug => $s->label()]),
        't' => __('calendar'),
    ];
    $uid = 'cal-'.\Illuminate\Support\Str::random(6);
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="{{ \App\Support\Asset::url('assets/css/calendar.css') }}">
    @endpush
    @push('scripts')
        {{-- Before Alpine starts: registers Alpine.data('sportCalendar') on alpine:init --}}
        <script src="{{ \App\Support\Asset::url('assets/js/calendar.js') }}"></script>
    @endpush
@endonce

<section {{ $attributes->class(['cal', 'cal--personal' => $personal]) }} x-data="sportCalendar(@js($config))"
         aria-labelledby="{{ $uid }}-month" :style="`--cal-ms: ${transitionMs}ms`">

    {{-- Antet: luna, navigare, „Azi” --}}
    <header class="cal__head">
        <h2 id="{{ $uid }}-month" class="t-headline cal__month" aria-live="polite" x-text="monthLabel"></h2>
        <div class="cal__nav">
            <button type="button" class="cal__icon-btn" x-on:click="shiftMonth(-1)" :disabled="!canPrev" aria-label="{{ __('calendar.prev') }}">
                <x-icon.arrow-left class="icon icon--sm" />
            </button>
            <button type="button" class="btn btn--secondary btn--sm cal__today" x-on:click="goToday()">{{ __('calendar.today') }}</button>
            <button type="button" class="cal__icon-btn" x-on:click="shiftMonth(1)" :disabled="!canNext" aria-label="{{ __('calendar.next') }}">
                <x-icon.arrow-right class="icon icon--sm" />
            </button>
        </div>
    </header>

    @if ($personal)
        <div class="segmented cal__toggle" role="group" aria-label="{{ __('calendar.title') }}">
            <button type="button" :aria-pressed="!onlyMine" x-on:click="setOnlyMine(false)">{{ __('calendar.all') }}</button>
            <button type="button" :aria-pressed="onlyMine" x-on:click="setOnlyMine(true)">{{ __('calendar.mine') }}</button>
        </div>
    @endif

    {{-- Grila: role="grid", rânduri cu celule; o singură zi are tabindex 0 (roving tabindex) --}}
    <div class="cal__grid" role="grid" aria-labelledby="{{ $uid }}-month" x-show="!firstLoad && !error"
         :class="{ 'is-loading': loading }" :aria-busy="loading ? 'true' : 'false'">
        <div class="cal__row cal__row--head" role="row">
            <template x-for="w in weekdays" :key="w.long">
                <span class="cal__weekday" role="columnheader"><abbr :title="w.long" x-text="w.short"></abbr></span>
            </template>
        </div>
        <template x-for="(week, wi) in weeks" :key="`${year}-${month}-${wi}`">
            <div class="cal__row" role="row">
                <template x-for="(cell, ci) in week" :key="cell ? cell.iso : `e-${wi}-${ci}`">
                    <div class="cal__cell" role="gridcell" :aria-selected="cell && cell.iso === selected ? 'true' : 'false'">
                        <template x-if="cell">
                            <button type="button" class="cal__day" :data-date="cell.iso"
                                    :class="{ 'is-past': cell.past, 'is-today': cell.iso === today, 'is-selected': cell.iso === selected,
                                              'has-matches': !!cell.info, 'is-mine': cell.info && cell.info.mine }"
                                    :tabindex="cell.iso === focusDate ? 0 : -1"
                                    :aria-disabled="cell.past ? 'true' : 'false'"
                                    :aria-current="cell.iso === today ? 'date' : null"
                                    :aria-label="dayLabel(cell)"
                                    x-on:click="select(cell.iso)" x-on:keydown="onKey($event, cell.iso)" x-on:focus="focusDate = cell.iso">
                                <span class="cal__num tabular" x-text="cell.day"></span>
                                <span class="cal__dots" aria-hidden="true">
                                    <template x-for="dot in dots(cell.info)" :key="dot.slug">
                                        <span :class="dot.plus ? 'cal__plus' : 'cal__dot'" :style="dot.plus ? '' : `background: ${dot.color}`"
                                              x-text="dot.plus ? '+' : ''"></span>
                                    </template>
                                </span>
                            </button>
                        </template>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- Încărcare: skeleton cu forma grilei --}}
    <div class="cal__skeleton" x-show="firstLoad && loading" role="status">
        <span class="sr-only">{{ __('calendar.loading') }}</span>
        @for ($i = 0; $i < 35; $i++)<span class="skeleton" aria-hidden="true"></span>@endfor
    </div>

    {{-- Eroare: mesaj clar + „Reîncearcă” --}}
    <div class="cal__error" x-show="error" x-cloak role="alert">
        <x-icon.circle-alert />
        <p class="t-callout">{{ __('calendar.error') }}</p>
        <button type="button" class="btn btn--secondary btn--sm" x-on:click="loadMonth()">{{ __('calendar.retry') }}</button>
    </div>

    {{-- Legenda --}}
    <p class="cal__legend t-footnote t-secondary">
        <span><span class="cal__dot cal__dot--legend" aria-hidden="true"></span>{{ __('calendar.legend_matches') }}</span>
        @if ($personal)
            <span><span class="cal__ring" aria-hidden="true"></span>{{ __('calendar.legend_mine') }}</span>
        @endif
    </p>

    {{-- Lista zilei alese (anunțată de cititoarele de ecran) --}}
    <div class="cal__day-panel" aria-live="polite">
        <p class="t-callout t-secondary" x-show="!selected">{{ __('calendar.pick_day') }}</p>

        <template x-if="selected">
            <div class="cal__list-wrap">
                <h3 class="t-headline" x-text="@js(__('calendar.day_title')).replace(':date', fullDate(selected))"></h3>

                <x-skeleton variant="line" :count="3" x-show="dayLoading" />

                <div class="cal__error" x-show="dayError && !dayLoading" role="alert">
                    <p class="t-callout">{{ __('calendar.error') }}</p>
                    <button type="button" class="btn btn--secondary btn--sm" x-on:click="loadDay()">{{ __('calendar.retry') }}</button>
                </div>

                <p class="field__error" x-show="actionError" x-cloak><x-icon.circle-alert /> <span x-text="actionError"></span></p>

                {{-- Zi fără meciuri: invitație, nu scuză --}}
                <div class="cal__empty" x-show="!dayLoading && !dayError && rooms.length === 0">
                    <p class="t-headline">{{ __('calendar.empty_title') }}</p>
                    <p class="t-callout t-secondary">{{ __('calendar.empty_text') }}</p>
                    <a class="btn btn--primary btn--sm" :href="createUrl"><x-icon.circle-plus class="icon icon--sm" /> {{ __('calendar.create') }}</a>
                </div>

                <ul class="cal__rooms" role="list" x-show="!dayLoading && !dayError && rooms.length > 0">
                    <template x-for="room in rooms" :key="room.id">
                        <li class="cal__room" :style="`--c: var(${room.sport.var})`">
                            <a class="cal__room-main" :href="room.url">
                                <span class="cal__room-time t-headline tabular" x-text="room.time"></span>
                                <span class="cal__room-text">
                                    <span class="t-footnote cal__room-sport" x-text="room.sport.name"></span>
                                    <span class="t-headline cal__room-title" x-text="room.title"></span>
                                    <span class="t-footnote t-secondary" x-text="[room.location, room.venue].filter(Boolean).join(' · ')"></span>
                                    <span class="t-footnote t-secondary tabular" x-text="`${room.spots} · ${room.price}`"></span>
                                </span>
                                <x-icon.chevron-right class="icon cal__chevron" />
                            </a>

                            @if ($personal)
                                <div class="cal__room-extra">
                                    <span class="badge" x-show="room.my_role_label"><span class="badge__dot" aria-hidden="true"></span><span x-text="room.my_role_label"></span></span>
                                    <span class="t-footnote t-secondary" x-show="room.price_detail" x-text="room.price_detail"></span>
                                    <span class="t-footnote t-secondary" x-show="room.equipment" x-text="room.equipment"></span>
                                    <span class="avatar-stack" x-show="room.players && room.players.length" :aria-label="@js(__('calendar.players'))">
                                        <template x-for="p in room.players" :key="p.name">
                                            <span>
                                                <template x-if="p.avatar"><img class="avatar avatar--sm avatar--photo" :src="p.avatar" :alt="p.name"></template>
                                                <template x-if="!p.avatar"><span class="avatar avatar--sm" role="img" :aria-label="p.name" x-text="p.initials"></span></template>
                                            </span>
                                        </template>
                                        <span class="avatar avatar--sm" x-show="room.players_more" x-text="`+${room.players_more}`"></span>
                                    </span>
                                    <div class="cal__actions" x-show="room.actions && (room.actions.join || room.actions.interest || room.actions.leave)">
                                        <button type="button" class="btn btn--primary btn--sm" x-show="room.actions && room.actions.join"
                                                :disabled="busy === room.id" x-on:click="act(room, 'join')">{{ __('match.show.join') }}</button>
                                        <button type="button" class="btn btn--secondary btn--sm" x-show="room.actions && room.actions.interest"
                                                :disabled="busy === room.id" x-on:click="act(room, 'interest')">{{ __('match.show.interest') }}</button>
                                        <button type="button" class="btn btn--secondary btn--sm" x-show="room.actions && room.actions.leave"
                                                :disabled="busy === room.id" x-on:click="act(room, 'leave')" x-text="room.actions && room.actions.leave_label"></button>
                                    </div>
                                </div>
                            @endif
                        </li>
                    </template>
                </ul>

                <a class="cal__see-all" :href="seeAllUrl" x-show="!dayLoading && rooms.length > 0">
                    {{ __('calendar.see_all') }} <x-icon.chevron-right class="icon icon--xs" />
                </a>
            </div>
        </template>
    </div>
</section>
