@php
    $errorsBag = $errorsBag ?? new \Illuminate\Support\MessageBag();
    $values = $values ?? [];
    $err = fn ($k) => $errorsBag->first($k);
    $val = fn ($k, $d = null) => $values[$k] ?? $d;
@endphp

<div class="panel">
    <div class="panel__head">
        <h2 class="panel__title">{{ __('landing.panel_edit_profile') }}</h2>
        <button type="button" class="panel__close" data-panel-close aria-label="{{ __('landing.panel_close') }}">&times;</button>
    </div>

    <form class="js-panel-form panel-form" method="POST" action="{{ route('profile.details.update') }}">
        @csrf
        @method('PATCH')

        <div class="panel-field">
            <label for="pp-bio">{{ __('landing.field_bio') }}</label>
            <textarea id="pp-bio" name="bio" rows="2">{{ $val('bio', $profile?->bio) }}</textarea>
            @if ($err('bio'))<p class="panel-error">{{ $err('bio') }}</p>@endif
        </div>

        <div class="panel-grid-2">
            <div class="panel-field">
                <label for="pp-city">{{ __('landing.filter_city') }}</label>
                <input type="text" id="pp-city" name="city" value="{{ $val('city', $profile?->city) }}">
                @if ($err('city'))<p class="panel-error">{{ $err('city') }}</p>@endif
            </div>
            <div class="panel-field">
                <label for="pp-skill">{{ __('landing.field_overall_skill') }}</label>
                <input type="number" id="pp-skill" name="skill_level" min="1" max="5" value="{{ $val('skill_level', $profile?->skill_level ?? 1) }}">
                @if ($err('skill_level'))<p class="panel-error">{{ $err('skill_level') }}</p>@endif
            </div>
        </div>

        <div class="panel-field">
            <label>{{ __('landing.field_sports') }}</label>
            <div class="panel-sports">
                @foreach ($sports as $sport)
                    @php $isSel = $selectedSkill->has($sport->id); @endphp
                    <div class="panel-sport">
                        <label class="panel-sport__name">
                            <input type="checkbox" name="sports[]" value="{{ $sport->id }}" @checked($isSel)>
                            <span>{{ $sport->name }}</span>
                        </label>
                        <input type="number" min="1" max="5" name="sport_skill[{{ $sport->id }}]"
                               value="{{ $selectedSkill->get($sport->id, 1) }}" aria-label="{{ $sport->name }} skill">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="panel__cta">
            <button type="submit" class="btn btn--primary">{{ __('landing.panel_save') }}</button>
        </div>
    </form>
</div>
