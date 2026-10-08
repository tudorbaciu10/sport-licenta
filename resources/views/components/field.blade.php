{{-- <x-field name="email" type="email" :label="__('…')" hint="…" autocomplete="email" required />
     <x-field name="city_id" type="select" :options="[id => name]" :label="…" />
     <x-field name="note" type="textarea" :label="…" optional />
     Wires label, hint, old() value and the validation error (with aria-invalid/-describedby).
     Pass a slot to render a custom control instead. Extra attributes go on the control. --}}
@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'options' => [],
    'placeholder' => null,
    'optional' => false,
    'id' => null,
])
@php
    $id = $id ?? 'f-'.str_replace(['[', ']', '.'], '-', $name);
    // $errors is shared on web requests; fall back to the session so the component also renders standalone.
    $bag = isset($errors) ? $errors : (session('errors') ?: new \Illuminate\Support\ViewErrorBag);
    $error = $bag->first(rtrim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.'));
    $value = old(rtrim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.'), $value);
    $describedBy = collect([$hint ? $id.'-hint' : null, $error ? $id.'-error' : null])->filter()->join(' ');
    $control = $attributes->merge(['id' => $id, 'name' => $name, 'class' => 'input'])
        ->merge($describedBy ? ['aria-describedby' => $describedBy] : [])
        ->merge($error ? ['aria-invalid' => 'true'] : []);
@endphp
<div @class(['field', 'field--error' => $error])>
    <label class="field__label" for="{{ $id }}">
        {{ $label }}@if ($optional) <span class="t-secondary">({{ __('components.field.optional') }})</span>@endif
    </label>

    @if ($slot->isNotEmpty())
        {{ $slot }}
    @elseif ($type === 'textarea')
        <textarea {{ $control }} @if ($placeholder) placeholder="{{ $placeholder }}" @endif>{{ $value }}</textarea>
    @elseif ($type === 'select')
        <div class="select">
            <select {{ $control }}>
                @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif
                @foreach ($options as $optValue => $optLabel)
                    <option value="{{ $optValue }}" @selected((string) $value === (string) $optValue)>{{ $optLabel }}</option>
                @endforeach
            </select>
        </div>
    @else
        <input type="{{ $type }}" value="{{ $value }}" {{ $control }} @if ($placeholder) placeholder="{{ $placeholder }}" @endif>
    @endif

    @if ($hint)
        <p class="field__hint" id="{{ $id }}-hint">{{ $hint }}</p>
    @endif
    @if ($error)
        <p class="field__error" id="{{ $id }}-error"><x-icon.circle-alert /> <span>{{ $error }}</span></p>
    @endif
</div>
