{{-- Month calendar of the create form (state in createMatch). Part of when-picker; alone in the phone month sheet. $id keeps ids unique. --}}
<div class="mcal" role="group" aria-labelledby="mcal-{{ $id }}-title">
    <div class="mcal__head">
        <button type="button" class="mcal__nav" x-on:click="shift(-1)" :disabled="!canPrev" aria-label="{{ __('match.create.prev_month') }}"><x-icon.arrow-left /></button>
        <h3 id="mcal-{{ $id }}-title" class="t-headline" aria-live="polite" x-text="monthLabel"></h3>
        <button type="button" class="mcal__nav" x-on:click="shift(1)" :disabled="!canNext" aria-label="{{ __('match.create.next_month') }}"><x-icon.arrow-right /></button>
    </div>
    <div class="mcal__grid">
        <div class="mcal__row">
            <template x-for="w in weekdays" :key="w.long">
                <span class="mcal__wd" aria-hidden="true" x-text="w.short"></span>
            </template>
        </div>
        <div class="mcal__days">
            <template x-for="c in cells" :key="c.date">
                <span class="mcal__cell">
                    <button type="button" class="mcal__day" :data-date="c.date"
                            :class="{ 'is-out': c.out, 'is-today': c.today, 'is-selected': c.selected, 'has-matches': c.count }"
                            :disabled="c.disabled" :tabindex="c.focus ? 0 : -1" :aria-pressed="c.selected ? 'true' : 'false'"
                            :aria-current="c.today ? 'date' : null"
                            :aria-label="c.label + (c.count ? ', ' + t.dayCount.replace(':count', c.count) : '')"
                            x-on:click="pickDate(c.date)" x-on:keydown="onKey($event, c.date)">
                        <span x-text="c.day"></span>
                        <span class="mcal__dots" aria-hidden="true">
                            <template x-for="s in c.sports" :key="s"><i :style="`background: ${dotColor(s)}`"></i></template>
                        </span>
                    </button>
                </span>
            </template>
        </div>
    </div>
</div>
