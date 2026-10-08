@extends('layouts.app')

@section('title', __('profile.edit'))

@section('content')
<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="profile profile-form">
    @csrf
    @method('PUT')

    <h1 class="t-large">{{ __('profile.edit') }}</h1>

    {{-- Photo with instant preview --}}
    <div @class(['field', 'field--error' => $errors->has('avatar')])
         x-data="{ preview: @js($user->avatarUrl()), remove: false,
                   pick(e) { const f = e.target.files[0]; if (f) { this.preview = URL.createObjectURL(f); this.remove = false; } } }">
        <p class="field__label">{{ __('profile.photo') }}</p>
        <div class="photo-pick">
            <template x-if="preview && !remove"><img :src="preview" alt="" class="avatar avatar--xl avatar--photo"></template>
            <template x-if="!preview || remove"><x-avatar :name="$user->name" size="xl" /></template>
            <div class="photo-pick__actions">
                <label class="btn btn--secondary btn--sm">
                    {{ __('profile.photo_change') }}
                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="sr-only" x-on:change="pick($event)">
                </label>
                @if ($user->avatar_path)
                    <label class="check t-callout"><input type="checkbox" name="remove_avatar" value="1" x-model="remove"> {{ __('profile.photo_remove') }}</label>
                @endif
                <p class="field__hint">{{ __('profile.photo_hint') }}</p>
            </div>
        </div>
        @error('avatar')<p class="field__error"><x-icon.circle-alert /> <span>{{ $message }}</span></p>@enderror
    </div>

    <x-field name="name" :label="__('profile.name')" :value="$user->name" autocomplete="name" maxlength="80" required />
    <x-field name="city_id" type="select" :label="__('profile.city')" :placeholder="__('profile.city_none')" :value="$user->city_id"
             :options="$cities->mapWithKeys(fn ($c) => [$c->id => $c->label()])->all()" />

    <section class="profile-section" aria-labelledby="sports-title">
        <div>
            <h2 id="sports-title" class="t-title2">{{ __('profile.sports') }}</h2>
            <p class="t-callout t-secondary measure">{{ __('profile.sports_hint') }}</p>
        </div>

        <div class="level-list">
            @foreach ($sports as $sport)
                @php($current = old("sports.{$sport->id}.level", $mine[$sport->id]->pivot->level ?? ''))
                <fieldset class="level-row" x-data="{ level: @js($current) }">
                    <legend class="level-row__head">
                        <x-sport-icon :sport="$sport" size="sm" />
                        <span class="t-headline">{{ $sport->label() }}</span>
                    </legend>
                    <div class="segmented" role="radiogroup" aria-label="{{ $sport->label() }}">
                        <label><input type="radio" name="sports[{{ $sport->id }}][level]" value="" x-model="level"><span>{{ __('profile.not_playing') }}</span></label>
                        @foreach (\App\Models\User::LEVELS as $level)
                            <label title="{{ __('profile.level_desc.'.$level) }}">
                                <input type="radio" name="sports[{{ $sport->id }}][level]" value="{{ $level }}" x-model="level">
                                <span>{{ __('profile.levels.'.$level) }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="t-footnote t-secondary" x-show="level" x-cloak
                       x-text="{ @foreach (\App\Models\User::LEVELS as $level)'{{ $level }}': @js(__('profile.level_desc.'.$level)), @endforeach }[level]"></p>
                    <div x-show="level" x-cloak>
                        <x-field :name="'sports['.$sport->id.'][position]'" :label="__('profile.position')" :placeholder="__('profile.position_ph')"
                                 :value="$mine[$sport->id]->pivot->position ?? null" maxlength="60" optional />
                    </div>
                </fieldset>
            @endforeach
        </div>
    </section>

    <div class="profile-form__actions">
        <x-button variant="secondary" :href="route('profile')">{{ __('profile.cancel') }}</x-button>
        <x-button type="submit">{{ __('profile.save') }}</x-button>
    </div>
</form>
@endsection
