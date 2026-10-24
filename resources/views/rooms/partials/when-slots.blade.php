{{-- Time slots of the create form (state in createMatch). Part of when-picker; alone under the week strip on phones. $id keeps ids unique. --}}
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
