{{-- Cookie banner: shown until the visitor chooses; reopened from the footer ("Setări cookie").
     Choice is saved in the "sportmd_consent" cookie (App\Support\Consent reads it on the server).
     Categories: necessary (always on) and preferences (remember the language). --}}
@php
    $config = [
        'cookie' => \App\Support\Consent::COOKIE,
        'localeCookie' => \App\Support\Consent::LOCALE_COOKIE,
        'version' => \App\Support\Consent::VERSION,
        'days' => \App\Support\Consent::DAYS,
        'locale' => app()->getLocale(),
    ];
@endphp
<div class="consent" x-data="cookieConsent(@js($config))" x-show="open" x-cloak
     x-transition:enter="consent-enter" x-transition:enter-start="consent-from" x-transition:enter-end="consent-to"
     x-on:open-cookie-settings.window="openSettings()"
     role="dialog" aria-modal="false" aria-labelledby="consent-title">

    {{-- First screen: short explanation + three choices --}}
    <div x-show="view === 'main'">
        <h2 id="consent-title" class="t-headline">{{ __('consent.title') }}</h2>
        <p class="t-callout t-secondary consent__text">
            {{ __('consent.text') }}
            <a href="{{ route('legal.cookies') }}">{{ __('consent.cookie_policy') }}</a> ·
            <a href="{{ route('legal.privacy') }}">{{ __('consent.privacy_policy') }}</a>
        </p>
        <div class="consent__actions">
            <button type="button" class="btn btn--primary btn--block" x-on:click="save(true)">{{ __('consent.accept_all') }}</button>
            <button type="button" class="btn btn--primary btn--block" x-on:click="save(false)">{{ __('consent.reject_all') }}</button>
            <button type="button" class="btn btn--secondary btn--block" x-on:click="view = 'settings'">{{ __('consent.customize') }}</button>
        </div>
    </div>

    {{-- Settings: one switch per category --}}
    <div x-show="view === 'settings'" x-cloak>
        <h2 class="t-headline">{{ __('consent.settings_title') }}</h2>
        <ul class="consent__list" role="list">
            <li>
                <div>
                    <p class="t-headline">{{ __('consent.categories.necessary.name') }}</p>
                    <p class="t-footnote t-secondary">{{ __('consent.categories.necessary.text') }}</p>
                </div>
                <span class="t-footnote t-secondary consent__always">{{ __('consent.always_on') }}</span>
            </li>
            <li>
                <label for="consent-preferences">
                    <span class="t-headline">{{ __('consent.categories.preferences.name') }}</span>
                    <span class="t-footnote t-secondary">{{ __('consent.categories.preferences.text') }}</span>
                </label>
                <input id="consent-preferences" type="checkbox" role="switch" class="switch" x-model="preferences">
            </li>
        </ul>
        <div class="consent__actions">
            <button type="button" class="btn btn--primary btn--block" x-on:click="save(preferences)">{{ __('consent.save') }}</button>
            <button type="button" class="btn btn--secondary btn--block" x-on:click="save(true)">{{ __('consent.accept_all') }}</button>
        </div>
    </div>
</div>
<p class="sr-only" role="status" x-data x-text="$store.consentSaved ? @js(__('consent.saved')) : ''"></p>

@once
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('consentSaved', false);

            Alpine.data('cookieConsent', (c) => ({
                open: false,
                view: 'main',
                preferences: false,

                init() {
                    const saved = this.read();
                    this.preferences = saved?.preferences === true;
                    this.open = !saved;                       // ask only until the visitor chooses
                },
                read() {
                    const raw = document.cookie.split('; ').find((p) => p.startsWith(c.cookie + '='));
                    try {
                        const data = raw ? JSON.parse(decodeURIComponent(raw.split('=').slice(1).join('='))) : null;
                        return data && data.v === c.version ? data : null;
                    } catch (e) {
                        return null;
                    }
                },
                setCookie(name, value, days) {
                    const secure = location.protocol === 'https:' ? '; Secure' : '';
                    document.cookie = `${name}=${encodeURIComponent(value)}; Max-Age=${days * 86400}; Path=/; SameSite=Lax${secure}`;
                },
                save(preferences) {
                    this.preferences = preferences;
                    this.setCookie(c.cookie, JSON.stringify({ v: c.version, preferences, date: new Date().toISOString().slice(0, 10) }), c.days);
                    // Preferences on: remember the current language; off: forget it right away.
                    if (preferences) this.setCookie(c.localeCookie, c.locale, 365);
                    else this.setCookie(c.localeCookie, '', 0);
                    this.open = false;
                    Alpine.store('consentSaved', true);
                },
                openSettings() {
                    this.view = 'settings';
                    this.open = true;
                    this.$nextTick(() => this.$el.querySelector('#consent-preferences')?.focus());
                },
            }));
        });
    </script>
    @endpush
@endonce
