{{-- Matches already planned on the chosen day in the chosen city: helps pick a free time / court. --}}
<section class="day-matches" aria-labelledby="day-matches-{{ $id }}" aria-live="polite">
    <h3 id="day-matches-{{ $id }}" class="t-headline">
        {{ __('match.create.day_matches') }}
        <span class="t-footnote t-secondary" x-show="date && citySlug && !dayLoading && dayRooms.length" x-text="t.dayCount.replace(':count', dayRooms.length)"></span>
    </h3>
    <p class="t-footnote t-secondary" x-show="!citySlug">{{ __('match.create.day_city') }}</p>
    <p class="t-footnote t-secondary" x-show="citySlug && !date">{{ __('match.create.day_pick') }}</p>
    <x-skeleton x-show="dayLoading" x-cloak :count="2" />
    <p class="t-footnote t-secondary" x-show="citySlug && date && !dayLoading && !dayRooms.length" x-cloak>{{ __('match.create.day_free') }}</p>
    <ul class="day-matches__list" role="list" x-show="!dayLoading && dayRooms.length" x-cloak>
        <template x-for="r in dayRooms" :key="r.id">
            <li>
                <a :href="r.url" class="day-match" target="_blank" :style="`--c: var(${r.sport.var})`">
                    <span class="day-match__time tabular" x-text="r.time"></span>
                    <span class="day-match__bar" aria-hidden="true"></span>
                    <span class="day-match__text">
                        <span class="day-match__title" x-text="r.title"></span>
                        <span class="t-footnote t-secondary" x-text="r.sport.name + ' · ' + r.location"></span>
                    </span>
                    <span class="day-match__same t-footnote" x-show="r.sport.slug === daySportSlug">{{ __('match.create.same_sport') }}</span>
                </a>
            </li>
        </template>
    </ul>
</section>
