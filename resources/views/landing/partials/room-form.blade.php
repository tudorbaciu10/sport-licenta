@php
    $errorsBag = $errorsBag ?? new \Illuminate\Support\MessageBag();
    $values = $values ?? [];
    $val = fn ($k, $d = '') => $values[$k] ?? $d;
    $err = fn ($k) => $errorsBag->first($k);
@endphp

<div class="panel">
    <div class="panel__head">
        <h2 class="panel__title">{{ __('landing.panel_create_event') }}</h2>
        <button type="button" class="panel__close" data-panel-close aria-label="{{ __('landing.panel_close') }}">&times;</button>
    </div>

    <form class="js-panel-form panel-form" method="POST" action="{{ route('events.store') }}">
        @csrf

        <div class="panel-field">
            <label for="pf-title">{{ __('landing.field_title') }}</label>
            <input type="text" id="pf-title" name="title" value="{{ $val('title') }}" required>
            @if ($err('title'))<p class="panel-error">{{ $err('title') }}</p>@endif
        </div>

        <div class="panel-grid-2">
            <div class="panel-field">
                <label for="pf-sport">{{ __('landing.field_sport') }}</label>
                <select id="pf-sport" name="sport_id" required>
                    <option value="">—</option>
                    @foreach ($sports as $sport)
                        <option value="{{ $sport->id }}" @selected($val('sport_id') == $sport->id)>{{ $sport->name }}</option>
                    @endforeach
                </select>
                @if ($err('sport_id'))<p class="panel-error">{{ $err('sport_id') }}</p>@endif
            </div>
            <div class="panel-field">
                <label for="pf-venue">{{ __('landing.field_venue') }}</label>
                <select id="pf-venue" name="venue_id">
                    <option value="">{{ __('landing.field_no_venue') }}</option>
                    @foreach ($venues as $venue)
                        <option value="{{ $venue->id }}" @selected($val('venue_id') == $venue->id)>{{ $venue->name }}</option>
                    @endforeach
                </select>
                @if ($err('venue_id'))<p class="panel-error">{{ $err('venue_id') }}</p>@endif
            </div>
        </div>

        <div class="panel-field">
            <label for="pf-desc">{{ __('landing.field_description') }}</label>
            <textarea id="pf-desc" name="description" rows="2">{{ $val('description') }}</textarea>
            @if ($err('description'))<p class="panel-error">{{ $err('description') }}</p>@endif
        </div>

        <div class="panel-grid-2">
            <div class="panel-field">
                <label for="pf-start">{{ __('landing.field_start') }}</label>
                <input type="datetime-local" id="pf-start" name="start_time" value="{{ $val('start_time') }}" required>
                @if ($err('start_time'))<p class="panel-error">{{ $err('start_time') }}</p>@endif
            </div>
            <div class="panel-field">
                <label for="pf-end">{{ __('landing.field_end') }}</label>
                <input type="datetime-local" id="pf-end" name="end_time" value="{{ $val('end_time') }}">
                @if ($err('end_time'))<p class="panel-error">{{ $err('end_time') }}</p>@endif
            </div>
        </div>

        <div class="panel-grid-3">
            <div class="panel-field">
                <label for="pf-city">{{ __('landing.filter_city') }}</label>
                <input type="text" id="pf-city" name="city" value="{{ $val('city') }}">
                @if ($err('city'))<p class="panel-error">{{ $err('city') }}</p>@endif
            </div>
            <div class="panel-field">
                <label for="pf-max">{{ __('landing.field_max') }}</label>
                <input type="number" id="pf-max" name="max_participants" min="2" max="1000" value="{{ $val('max_participants') }}">
                @if ($err('max_participants'))<p class="panel-error">{{ $err('max_participants') }}</p>@endif
            </div>
            <div class="panel-field">
                <label for="pf-skill">{{ __('landing.field_skill') }}</label>
                <input type="number" id="pf-skill" name="skill_level" min="1" max="5" value="{{ $val('skill_level') }}">
                @if ($err('skill_level'))<p class="panel-error">{{ $err('skill_level') }}</p>@endif
            </div>
        </div>

        <div class="panel__cta">
            <button type="submit" class="btn btn--primary">{{ __('landing.panel_create_event') }}</button>
        </div>
    </form>
</div>
