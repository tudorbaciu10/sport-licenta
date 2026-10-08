{{-- One step of <x-step-form>. Hidden by Alpine unless current; all visible without JS. --}}
@props(['n'])
<fieldset {{ $attributes->class('step-form__step') }} data-step="{{ $n }}" x-show="step === {{ $n }}">
    {{ $slot }}
</fieldset>
