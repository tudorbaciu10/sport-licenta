{{-- <x-step-form :steps="['Sport și oraș', 'Când și unde', …]" action="…" :submit="__('…')">
         <x-step-form.step :n="1"> …fields… </x-step-form.step> …
     </x-step-form>
     One small group of fields per step. "Continuă" checks the current step's fields and writes the
     error under each field, in the page language (lang/{ro,ru}/validation.php), not in the browser's.
     A parent Alpine component may add its own checks with checkStep(step) → [{ name, message }].
     After a server error it opens the first step that has one.
     Without JS every step shows on one page, so the form still works. --}}
@props(['steps', 'action', 'method' => 'POST', 'submit'])
@php
    // Message templates for the browser checks; :attribute comes from validation.attributes.
    $messages = [
        'required' => __('validation.required'),
        'choose' => __('validation.required_unless'),
        'min' => __('validation.min.numeric'),
        'max' => __('validation.max.numeric'),
        'maxlength' => __('validation.max.string'),
        'number' => __('validation.integer'),
        'invalid' => __('validation.in'),
        'attributes' => __('validation.attributes'),
        'custom' => __('validation.custom'),
    ];
@endphp
<form method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}" action="{{ $action }}" novalidate
      {{ $attributes->class('step-form') }} x-data="stepForm({{ count($steps) }}, @js($messages))" @submit="submit($event)"
      x-on:goto-step.window="go($event.detail)">
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
        @isset($navSummary)
            <div class="step-form__summary m-only">{{ $navSummary }}</div>
        @endisset
        <x-button variant="ghost" icon="arrow-left" x-show="step > 1" x-cloak x-on:click="back()"><span class="step-form__back-label">{{ __('components.step.back') }}</span></x-button>
        <x-button variant="primary" icon-right="arrow-right" x-show="step < total" x-cloak x-on:click="next()">{{ __('components.step.next') }}</x-button>
        <x-button variant="primary" type="submit" x-show="step === total" class="step-form__submit">{{ $submit }}</x-button>
    </div>

    <template x-ref="errorTpl"><p class="field__error" data-client><x-icon.circle-alert /> <span></span></p></template>
</form>

@once
    @push('scripts')
    <script>
        function stepForm(total, messages) {
            return {
                step: 1,
                total,
                init() {
                    this.$root.classList.add('is-enhanced');
                    // Server-side errors: open the first step that contains one.
                    const err = this.$root.querySelector('.field--error');
                    if (err) this.step = +err.closest('[data-step]').dataset.step;
                    // An error goes away as soon as the field is changed.
                    const clear = (e) => { const f = e.target.closest('.field'); if (f) this.clearError(f); };
                    this.$root.addEventListener('input', clear);
                    this.$root.addEventListener('change', clear);
                    this.$watch('step', (s) => this.$dispatch('step-changed', s));
                },
                fields(step = this.step) {
                    return [...this.$root.querySelectorAll(`[data-step="${step}"] :is(input, select, textarea)`)];
                },

                // Laravel-style key for a field: "rules[]" → "rules.*", "sports[3][level]" → "sports.*.level".
                key(name) {
                    return name.replace(/\[\]$/, '.*').replace(/\[(\d+)\]/g, '.*').replace(/\[(\w+)\]/g, '.$1');
                },
                attribute(field) {
                    const k = this.key(field.name);
                    const label = field.closest('.field')?.querySelector('.field__label, .group-label')?.firstChild?.textContent;
                    return messages.attributes[k] || messages.attributes[field.name] || (label || field.name).trim().toLowerCase();
                },
                message(field) {
                    const v = field.validity;
                    const k = this.key(field.name);
                    const custom = messages.custom[k] || messages.custom[field.name] || {};
                    const choice = field.type === 'radio' || field.tagName === 'SELECT';
                    let rule, text;
                    if (v.valueMissing) { rule = 'required'; text = custom.required || messages[choice ? 'choose' : 'required']; }
                    else if (v.badInput || v.typeMismatch || v.stepMismatch && field.type !== 'number') { text = messages.number; }
                    else if (v.rangeUnderflow) { text = custom.min || messages.min; }
                    else if (v.rangeOverflow) { text = custom.max || messages.max; }
                    else if (v.tooLong) { text = messages.maxlength; }
                    else if (v.stepMismatch) { return null; }   // e.g. 13 lei with step 5: allowed, the server accepts it
                    else { text = messages.invalid; }
                    const attr = this.attribute(field);
                    return text
                        .replace(':Attribute', attr.charAt(0).toUpperCase() + attr.slice(1))
                        .replace(':attribute', attr)
                        .replace(':min', field.min).replace(':max', field.max || field.maxLength);
                },

                showError(field, text) {
                    const box = field.closest('.field');
                    if (!box) return;
                    box.classList.add('field--error');
                    let p = box.querySelector('.field__error');
                    if (!p) {
                        p = this.$refs.errorTpl.content.firstElementChild.cloneNode(true);
                        p.id = 'err-' + (field.id || field.name.replace(/\W/g, '-'));
                        box.append(p);
                    }
                    p.querySelector('span').textContent = text;
                    box.querySelectorAll(`[name="${CSS.escape(field.name)}"]`).forEach((el) => {
                        el.setAttribute('aria-invalid', 'true');
                        el.setAttribute('aria-describedby', p.id);
                    });
                },
                clearError(box) {
                    if (!box.classList.contains('field--error')) return;
                    box.classList.remove('field--error');
                    box.querySelectorAll('.field__error').forEach((p) => p.remove());
                    box.querySelectorAll('[aria-invalid]').forEach((el) => el.removeAttribute('aria-invalid'));
                },

                // Checks one step; writes every error, returns the first bad field.
                validate(step = this.step) {
                    let first = null;
                    const seen = new Set();
                    for (const f of this.fields(step)) {
                        // Skip fields hidden by a condition inside the step (e.g. "who collects" when free).
                        const cond = f.closest('[x-show]:not([data-step])');
                        if (!f.name || seen.has(f.name) || f.disabled || cond?.style.display === 'none') continue;
                        seen.add(f.name);
                        const text = f.checkValidity() ? null : this.message(f);
                        if (text) { this.showError(f, text); first ??= f; }
                        else this.clearError(f.closest('.field') ?? document.createElement('div'));
                    }
                    for (const { name, message } of (this.checkStep?.(step) ?? [])) {
                        const f = this.$root.querySelector(`[data-step="${step}"] [name="${name}"]`);
                        if (!f) continue;
                        this.showError(f, message);
                        first ??= f;
                    }
                    return first;
                },
                focusBad(field) {
                    const target = field.type === 'hidden' ? field.closest('.field')?.querySelector('button, input:not([type=hidden])') : field;
                    target?.focus({ preventScroll: true });
                    field.closest('.field')?.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'center' });
                },
                next() {
                    const bad = this.validate();
                    if (bad) { this.focusBad(bad); return false; }
                    this.step++;
                    this.$nextTick(() => this.$root.querySelector(`[data-step="${this.step}"] :is(input:not([type=hidden]), select, textarea)`)?.focus({ preventScroll: true }));
                    return true;
                },
                back() { this.step--; },
                // Jump from outside (summary list): back freely, forward only through valid steps.
                go(n) {
                    while (this.step < n) { if (!this.next()) return; }
                    if (n < this.step) this.step = n;
                },
                submit(e) {
                    for (let s = 1; s <= this.total; s++) {
                        const bad = this.validate(s);
                        if (bad) { e.preventDefault(); this.step = s; this.$nextTick(() => this.focusBad(bad)); return; }
                    }
                },
            };
        }
    </script>
    @endpush
@endonce
