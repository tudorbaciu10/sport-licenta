{{-- Day + time picker of the create form (state lives in createMatch, public/assets/js/create-match.js).
     Drawn twice: inside step 2 on phones/tablets, in the right column on laptops. $id keeps ids unique. --}}
<div class="when-picker">
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

    <div class="slots">
        <div class="slots__head">
            <p class="group-label" id="slots-{{ $id }}">{{ __('match.create.time') }}</p>
            <div class="segmented slots__parts" role="group" aria-labelledby="slots-{{ $id }}">
                @foreach (__('match.create.parts') as $key => $label)
                    <button type="button" :aria-pressed="part === '{{ $key }}' ? 'true' : 'false'"
                            :class="part === '{{ $key }}' && 'is-on'" x-on:click="part = '{{ $key }}'">{{ $label }}</button>
                @endforeach
            </div>
        </div>
        <div class="slots__grid" role="group" aria-labelledby="slots-{{ $id }}">
            <template x-for="s in slots" :key="s">
                <button type="button" class="slot" :class="time === s && 'is-selected'" :disabled="slotDisabled(s)"
                        :aria-pressed="time === s ? 'true' : 'false'" x-on:click="pickTime(s)" x-text="s"></button>
            </template>
        </div>
        {{-- Any other time, always 24h (the native time field follows the browser language: "07:00 PM") --}}
        <div class="slots__other" role="group" aria-labelledby="other-{{ $id }}">
            <span id="other-{{ $id }}" class="t-footnote t-secondary">{{ __('match.create.other_time') }}</span>
            <div class="select slots__select">
                <select class="input" :value="time.slice(0, 2)" x-on:change="setOther($event.target.value, null)" aria-label="{{ __('match.create.time') }} (h)">
                    <option value="" disabled :selected="!time">--</option>
                    @for ($h = 6; $h <= 23; $h++)<option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>@endfor
                </select>
            </div>
            <span aria-hidden="true">:</span>
            <div class="select slots__select">
                <select class="input" :value="time.slice(3, 5)" x-on:change="setOther(null, $event.target.value)" aria-label="{{ __('match.create.time') }} (min)">
                    <option value="" disabled :selected="!time">--</option>
                    @for ($m = 0; $m < 60; $m += 5)<option value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>@endfor
                </select>
            </div>
        </div>
    </div>
</div>
