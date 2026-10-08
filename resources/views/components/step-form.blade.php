{{-- <x-step-form :steps="['Sport și oraș', 'Când și unde', …]" action="…" :submit="__('…')">
         <x-step-form.step :n="1"> …fields… </x-step-form.step> …
     </x-step-form>
     One small group of fields per step. "Continuă" checks the current step's fields with the
     browser's own validation. After a server error it opens the first step that has one.
     Without JS every step shows on one page, so the form still works. --}}
@props(['steps', 'action', 'method' => 'POST', 'submit'])
<form method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}" action="{{ $action }}" novalidate
      {{ $attributes->class('step-form') }} x-data="stepForm({{ count($steps) }})" @submit="submit($event)">
    @if (strtoupper($method) !== 'GET')
        @csrf
        @unless (in_array(strtoupper($method), ['GET', 'POST']))
            @method($method)
        @endunless
    @endif

    <div class="step-form__progress" x-show="true" x-cloak>
        <p class="t-footnote t-secondary" aria-live="polite">
            @foreach ($steps as $i => $title)
                <span x-show="step === {{ $i + 1 }}">{{ __('components.step.progress', ['n' => $i + 1, 'total' => count($steps)]) }} · <strong class="step-form__title">{{ $title }}</strong></span>
            @endforeach
        </p>
        <div class="step-form__bar" aria-hidden="true">
            @foreach ($steps as $i => $title)
                <span :class="step >= {{ $i + 1 }} && 'is-done'"></span>
            @endforeach
        </div>
    </div>

    {{ $slot }}

    <div class="step-form__nav">
        <x-button variant="ghost" icon="arrow-left" x-show="step > 1" x-cloak x-on:click="back()">{{ __('components.step.back') }}</x-button>
        <x-button variant="primary" icon-right="arrow-right" x-show="step < total" x-cloak x-on:click="next()">{{ __('components.step.next') }}</x-button>
        <x-button variant="primary" type="submit" x-show="step === total" class="step-form__submit">{{ $submit }}</x-button>
    </div>
</form>

@once
    @push('scripts')
    <script>
        function stepForm(total) {
            return {
                step: 1,
                total,
                init() {
                    this.$root.classList.add('is-enhanced');
                    // Server-side errors: open the first step that contains one.
                    const err = this.$root.querySelector('.field--error');
                    if (err) this.step = +err.closest('[data-step]').dataset.step;
                },
                fields() {
                    return [...this.$root.querySelectorAll(`[data-step="${this.step}"] :is(input, select, textarea)`)];
                },
                next() {
                    const bad = this.fields().find((f) => !f.checkValidity());
                    if (bad) { bad.reportValidity(); bad.focus(); return; }
                    this.step++;
                    this.$nextTick(() => this.$root.querySelector(`[data-step="${this.step}"] :is(input, select, textarea)`)?.focus());
                },
                back() { this.step--; },
                submit(e) {
                    const bad = this.fields().find((f) => !f.checkValidity());
                    if (bad) { e.preventDefault(); bad.reportValidity(); }
                },
            };
        }
    </script>
    @endpush
@endonce
