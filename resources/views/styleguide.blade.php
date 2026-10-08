@extends('layouts.app')

@section('title', __('styleguide.title'))

@php
    $base = [
        ['--bg', 'bg'], ['--bg-subtle', 'bg-subtle'], ['--text', 'text'], ['--text-2', 'text-2'],
        ['--separator', 'separator'], ['--brand', 'brand'], ['--brand-hover', 'brand-hover'], ['--on-brand', 'on-brand'],
    ];
    $status = ['--success', '--danger', '--warning', '--full'];
    $sports = \App\Models\Sport::orderBy('name')->get();
    $type = [
        ['t-large', '34/41 · 700'], ['t-title1', '28/34 · 700'], ['t-title2', '22/28 · 600'],
        ['t-headline', '17/22 · 600'], ['t-body', '17/24 · 400'], ['t-callout', '15/20 · 400'],
        ['t-footnote', '13/18 · 400'],
    ];
    $sportCards = \App\Models\Sport::withCount(['rooms as open_count' => fn ($q) => $q->upcoming()])->orderBy('name')->get();
    $sample = \App\Models\Room::upcoming()->where('status', 'open')->with(['sport', 'city'])->orderBy('match_date_time')->first();
    $samples = $sample ? [
        $sample,
        (clone $sample)->forceFill(['current_players_count' => max(1, $sample->max_players - 1)]),
        (clone $sample)->forceFill(['current_players_count' => $sample->max_players, 'status' => \App\Enums\RoomStatus::Full]),
    ] : [];
    $icons = collect(glob(resource_path('views/components/icon/*.blade.php')))
        ->map(fn ($f) => basename($f, '.blade.php'))->values();
@endphp

@push('styles')
<style>
    /* Styleguide-only layout. Tokens only. */
    .sg-section { padding-block: var(--space-6); border-top: 1px solid var(--separator); }
    .sg-section:first-of-type { border-top: 0; padding-top: 0; }
    .sg-section > h2 { margin-bottom: var(--space-2); }
    .sg-section > p { margin-bottom: var(--space-5); }
    .sg-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: var(--space-3); }
    .sg-swatch { border: 1px solid var(--separator); border-radius: var(--radius-card); overflow: hidden; }
    .sg-swatch__color { height: 64px; background: var(--c); border-bottom: 1px solid var(--separator); }
    .sg-swatch__meta { padding: var(--space-3); }
    .sg-swatch__meta code { font-size: var(--fs-footnote); line-height: var(--lh-footnote); display: block; overflow-wrap: anywhere; }

    .sg-grid.sg-sports { grid-template-columns: minmax(0, 1fr); }
    .sg-sport { display: flex; flex-direction: column; gap: var(--space-3); padding: var(--space-4); border: 1px solid var(--separator); border-radius: var(--radius-card); }
    .sg-sport__row { display: flex; gap: var(--space-3); align-items: center; }
    .sg-sport__solid, .sg-sport__tint {
        width: var(--tap); height: var(--tap); border-radius: var(--radius-icon); flex: none;
        display: grid; place-items: center; font-weight: var(--fw-bold); font-size: var(--fs-headline);
    }
    .sg-sport__solid { background: var(--c); color: var(--on); }
    .sg-sport__tint { background: color-mix(in srgb, var(--c) var(--tint), transparent); }
    .sg-sport__band { height: 6px; border-radius: var(--radius-pill); background: var(--c); }
    .sg-ok { color: var(--text); }
    .sg-ok .icon, .sg-bad .icon { width: 16px; height: 16px; display: inline-block; vertical-align: -3px; }
    .sg-bad .icon { color: var(--danger); }

    .sg-type > div { display: grid; gap: var(--space-1); padding-block: var(--space-3); border-bottom: 1px solid var(--separator); }
    .sg-space { display: grid; gap: var(--space-2); }
    .sg-space div { display: flex; align-items: center; gap: var(--space-3); }
    .sg-space span:first-child { height: var(--space-4); background: var(--brand); border-radius: var(--space-1); }
    .sg-radii { display: flex; flex-wrap: wrap; gap: var(--space-4); }
    .sg-radii div { width: 88px; height: 64px; background: var(--bg-subtle); border: 1px solid var(--separator); display: grid; place-items: center; }
    .sg-row { display: flex; flex-wrap: wrap; gap: var(--space-3); align-items: center; }
    .sg-form { display: grid; gap: var(--space-5); max-width: 480px; }
    .sg-contents { display: contents; }
    .sg-icons { display: grid; grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)); gap: var(--space-3); }
    .sg-icons div { display: grid; justify-items: center; gap: var(--space-2); padding: var(--space-4) var(--space-2); background: var(--bg-subtle); border-radius: var(--radius-control); }

    @media (min-width: 768px) {
        .sg-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .sg-grid.sg-sports { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (min-width: 1024px) {
        .sg-grid.sg-sports { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
</style>
@endpush

@section('content')
    <header class="stack" style="--stack: var(--space-2); margin-bottom: var(--space-6)">
        <h1 class="t-large">{{ __('styleguide.title') }}</h1>
        <p class="t-body t-secondary measure">{{ __('styleguide.intro') }}</p>
    </header>

    <section class="sg-section" aria-labelledby="sg-base">
        <h2 id="sg-base" class="t-title2">{{ __('styleguide.colors_base') }}</h2>
        <div class="sg-grid" style="margin-top: var(--space-4)">
            @foreach ($base as [$token])
                <div class="sg-swatch" style="--c: var({{ $token }})">
                    <div class="sg-swatch__color"></div>
                    <div class="sg-swatch__meta">
                        <code class="t-headline">{{ $token }}</code>
                        <code class="t-secondary" data-hex="{{ $token }}"></code>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-status">
        <h2 id="sg-status" class="t-title2">{{ __('styleguide.colors_status') }}</h2>
        <p class="t-callout t-secondary measure">{{ __('styleguide.colors_status_note') }}</p>
        <div class="sg-grid">
            @foreach ($status as $token)
                <div class="sg-swatch" style="--c: var({{ $token }})">
                    <div class="sg-swatch__color"></div>
                    <div class="sg-swatch__meta">
                        <code class="t-headline">{{ $token }}</code>
                        <code class="t-secondary" data-hex="{{ $token }}"></code>
                        <code class="t-secondary" data-contrast="{{ $token }}|--bg"></code>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-sports">
        <h2 id="sg-sports" class="t-title2">{{ __('styleguide.sports') }}</h2>
        <p class="t-callout t-secondary measure">{{ __('styleguide.sports_note') }}</p>
        <div class="sg-grid sg-sports">
            @foreach ($sports as $sport)
                @php $var = $sport->cssVar(); @endphp
                <div class="sg-sport" style="--c: var({{ $var }}); --on: var(--on-{{ substr($var, 2) }})">
                    <div class="sg-sport__band"></div>
                    <div class="sg-sport__row">
                        <span class="sg-sport__solid" aria-hidden="true">{{ mb_substr($sport->name, 0, 1) }}</span>
                        <span class="sg-sport__tint" aria-hidden="true"></span>
                        <div>
                            <div class="t-headline">{{ $sport->name }}</div>
                            <code class="t-footnote t-secondary">{{ $var }}</code>
                        </div>
                    </div>
                    <div class="t-footnote">
                        <span class="t-secondary">{{ __('styleguide.contrast') }}:</span>
                        <span data-contrast="{{ $var }}|--on-{{ substr($var, 2) }}" data-large="{{ __('styleguide.large_only') }}"></span>
                    </div>
                    <div class="t-footnote" data-db="{{ $var }}" data-db-color="{{ $sport->color }}"
                         data-ok="{{ __('styleguide.db_match') }}" data-bad="{{ __('styleguide.db_mismatch') }}"></div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-type">
        <h2 id="sg-type" class="t-title2">{{ __('styleguide.type') }}</h2>
        <div class="sg-type">
            @foreach ($type as [$class, $spec])
                <div>
                    <span class="t-footnote t-secondary">{{ __('styleguide.type_names.'.$class) }} · {{ $spec }} · .{{ $class }}</span>
                    <span class="{{ $class }}">{{ __('styleguide.type_sample') }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-space">
        <h2 id="sg-space" class="t-title2">{{ __('styleguide.spacing') }}</h2>
        <div class="sg-space" style="margin-top: var(--space-4)">
            @foreach (range(1, 8) as $i)
                <div><span style="width: var(--space-{{ $i }})"></span><code class="t-footnote t-secondary">--space-{{ $i }} · <span data-hex="--space-{{ $i }}"></span></code></div>
            @endforeach
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-radii">
        <h2 id="sg-radii" class="t-title2">{{ __('styleguide.radii') }}</h2>
        <div class="sg-radii" style="margin-top: var(--space-4)">
            @foreach (['--radius-card', '--radius-control', '--radius-icon', '--radius-pill'] as $r)
                <div style="border-radius: var({{ $r }})"><code class="t-footnote">{{ str_replace('--radius-', '', $r) }}</code></div>
            @endforeach
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-buttons">
        <h2 id="sg-buttons" class="t-title2">{{ __('styleguide.buttons') }}</h2>
        <div class="stack" style="margin-top: var(--space-4)">
            <div class="sg-row">
                <button class="btn btn--primary">{{ __('styleguide.primary') }}</button>
                <button class="btn btn--secondary">{{ __('styleguide.secondary') }}</button>
                <button class="btn btn--ghost"><x-icon.chevron-right class="icon icon--sm" /> {{ __('styleguide.ghost') }}</button>
            </div>
            <div class="sg-row">
                <button class="btn btn--primary" disabled>{{ __('styleguide.disabled') }}</button>
                <button class="btn btn--secondary btn--sm">{{ __('styleguide.secondary') }}</button>
            </div>
            <div style="max-width: 390px">
                <button class="btn btn--primary btn--block">{{ __('styleguide.primary') }}</button>
            </div>
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-fields">
        <h2 id="sg-fields" class="t-title2">{{ __('styleguide.fields') }}</h2>
        <form class="sg-form" style="margin-top: var(--space-4)" onsubmit="return false">
            <div class="field">
                <label class="field__label" for="sg-name">{{ __('styleguide.field_name') }}</label>
                <input class="input" id="sg-name" placeholder="{{ __('styleguide.field_name_ph') }}" autocomplete="name">
            </div>
            <div class="field field--error">
                <label class="field__label" for="sg-email">{{ __('styleguide.field_email') }}</label>
                <input class="input" id="sg-email" type="email" value="ion@" aria-invalid="true" aria-describedby="sg-email-err" autocomplete="email">
                <p class="field__error" id="sg-email-err"><x-icon.circle-alert /> {{ __('styleguide.field_email_error') }}</p>
            </div>
            <div class="field">
                <label class="field__label" for="sg-city">{{ __('styleguide.field_city') }}</label>
                <div class="select">
                    <select class="input" id="sg-city">
                        @foreach (\App\Models\City::orderBy('name')->pluck('name') as $city)
                            <option>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="field">
                <label class="field__label" for="sg-note">{{ __('styleguide.field_note') }}</label>
                <textarea class="input" id="sg-note" aria-describedby="sg-note-hint"></textarea>
                <p class="field__hint" id="sg-note-hint">{{ __('styleguide.field_note_hint') }}</p>
            </div>
            <label class="check"><input type="checkbox" checked> {{ __('styleguide.field_check') }}</label>
        </form>
    </section>

    {{-- ======== Components (Faza 2) ======== --}}
    <section class="sg-section" aria-labelledby="sg-c-button">
        <p class="t-footnote t-secondary">{{ __('styleguide.components') }}</p>
        <h2 id="sg-c-button" class="t-title2">{{ __('styleguide.c_button') }} <code class="t-footnote t-secondary">&lt;x-button&gt;</code></h2>
        <div class="sg-row" style="margin-top: var(--space-4)">
            <x-button>{{ __('styleguide.primary') }}</x-button>
            <x-button variant="secondary" icon="share-2">{{ __('styleguide.ghost') }}</x-button>
            <x-button variant="ghost" icon-right="chevron-right" href="#">{{ __('styleguide.secondary') }}</x-button>
            <x-button disabled>{{ __('styleguide.disabled') }}</x-button>
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-c-chip">
        <h2 id="sg-c-chip" class="t-title2">{{ __('styleguide.c_chip') }} <code class="t-footnote t-secondary">&lt;x-chip&gt;</code></h2>
        <div class="chips" style="margin-top: var(--space-4)">
            <x-chip href="#" active>{{ __('styleguide.chip_all') }}</x-chip>
            <x-chip href="#">{{ __('styleguide.chip_today') }}</x-chip>
            <x-chip href="#">{{ __('styleguide.chip_tomorrow') }}</x-chip>
            <x-chip href="#">{{ __('styleguide.chip_weekend') }}</x-chip>
            <x-chip href="#">{{ __('styleguide.chip_indoor') }}</x-chip>
            <x-chip href="#">{{ __('styleguide.chip_outdoor') }}</x-chip>
            <x-chip>{{ __('styleguide.chip_free') }}</x-chip>
        </div>
        <div class="chips">
            @foreach ($sportCards->take(4) as $sport)
                <x-chip href="#" :sport="$sport" :active="$loop->first">{{ $sport->label() }}</x-chip>
            @endforeach
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-c-icon">
        <h2 id="sg-c-icon" class="t-title2">{{ __('styleguide.c_sport_icon') }} <code class="t-footnote t-secondary">&lt;x-sport-icon&gt;</code></h2>
        <div class="sg-row" style="margin-top: var(--space-4)">
            @foreach ($sportCards as $sport)
                <span class="sg-row" style="gap: var(--space-2)" title="{{ $sport->label() }}">
                    <x-sport-icon :sport="$sport" size="lg" />
                </span>
            @endforeach
        </div>
        <div class="sg-row" style="margin-top: var(--space-3)">
            @foreach ($sportCards as $sport)
                <x-sport-icon :sport="$sport" size="md" />
            @endforeach
            @foreach ($sportCards as $sport)
                <x-sport-icon :sport="$sport" size="sm" />
            @endforeach
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-c-sport-card">
        <h2 id="sg-c-sport-card" class="t-title2">{{ __('styleguide.c_sport_card') }} <code class="t-footnote t-secondary">&lt;x-sport-card&gt;</code></h2>
        <div class="sport-grid" style="margin-top: var(--space-4)">
            @foreach ($sportCards as $sport)
                <x-sport-card :sport="$sport" :count="$sport->open_count" />
            @endforeach
        </div>
    </section>

    @if ($samples)
        <section class="sg-section" aria-labelledby="sg-c-match">
            <h2 id="sg-c-match" class="t-title2">{{ __('styleguide.c_match_card') }} <code class="t-footnote t-secondary">&lt;x-match-card&gt;</code></h2>
            <div class="match-list" style="margin-top: var(--space-4)">
                @foreach ($samples as $room)
                    <x-match-card :room="$room" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="sg-section" aria-labelledby="sg-c-badge">
        <h2 id="sg-c-badge" class="t-title2">{{ __('styleguide.c_badge') }} <code class="t-footnote t-secondary">&lt;x-badge&gt;</code></h2>
        <div class="sg-row" style="margin-top: var(--space-4)">
            <x-badge status="open" /> <x-badge status="almost" /> <x-badge status="full" />
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-c-avatar">
        <h2 id="sg-c-avatar" class="t-title2">{{ __('styleguide.c_avatar') }} <code class="t-footnote t-secondary">&lt;x-avatar&gt;</code></h2>
        <div class="sg-row" style="margin-top: var(--space-4)">
            <x-avatar name="Ion Popescu" size="lg" />
            <x-avatar name="Cristina Munteanu" />
            <x-avatar name="Victor Rusu" size="sm" />
            <x-avatar name="Ana Lungu" highlight />
            <span class="avatar-stack">
                @foreach (['Ion P', 'Ana L', 'Dan S', 'Elena C'] as $n)<x-avatar :name="$n" size="sm" />@endforeach
            </span>
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-c-field">
        <h2 id="sg-c-field" class="t-title2">{{ __('styleguide.c_field') }} <code class="t-footnote t-secondary">&lt;x-field&gt;</code></h2>
        <div class="sg-form" style="margin-top: var(--space-4)">
            <x-field name="sg_name" :label="__('styleguide.field_name')" :placeholder="__('styleguide.field_name_ph')" autocomplete="name" />
            <x-field name="sg_city" type="select" :label="__('styleguide.field_city')" :placeholder="__('styleguide.f_choose')"
                     :options="\App\Models\City::orderBy('name')->pluck('name', 'id')->all()" />
            <x-field name="sg_note" type="textarea" :label="__('styleguide.field_note')" :hint="__('styleguide.field_note_hint')" optional />
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-c-nav">
        <h2 id="sg-c-nav" class="t-title2">{{ __('styleguide.c_bottom_nav') }} <code class="t-footnote t-secondary">&lt;x-bottom-nav&gt;</code></h2>
        <p class="t-callout t-secondary">{{ __('styleguide.c_bottom_nav_note') }}</p>
    </section>

    <section class="sg-section" aria-labelledby="sg-c-empty">
        <h2 id="sg-c-empty" class="t-title2">{{ __('styleguide.c_empty') }} <code class="t-footnote t-secondary">&lt;x-empty-state&gt;</code></h2>
        <x-empty-state :title="__('styleguide.empty_title')" :text="__('styleguide.empty_text')" style="margin-top: var(--space-4)">
            <x-button icon="circle-plus" href="#">{{ __('styleguide.empty_action') }}</x-button>
        </x-empty-state>
    </section>

    <section class="sg-section" aria-labelledby="sg-c-skeleton">
        <h2 id="sg-c-skeleton" class="t-title2">{{ __('styleguide.c_skeleton') }} <code class="t-footnote t-secondary">&lt;x-skeleton&gt;</code></h2>
        <div class="stack" style="margin-top: var(--space-4)">
            <x-skeleton variant="sport-card" :count="4" />
            <div class="match-list"><x-skeleton variant="match-card" :count="2" class="sg-contents" /></div>
            <x-skeleton :count="3" style="max-width: 480px" />
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-c-step">
        <h2 id="sg-c-step" class="t-title2">{{ __('styleguide.c_step_form') }} <code class="t-footnote t-secondary">&lt;x-step-form&gt;</code></h2>
        <div style="max-width: 480px; margin-top: var(--space-4)">
            <x-step-form :steps="[__('styleguide.step_1'), __('styleguide.step_2'), __('styleguide.step_3')]"
                         action="#" method="GET" :submit="__('styleguide.step_submit')" onsubmit="event.preventDefault()">
                <x-step-form.step :n="1">
                    <x-field name="demo_sport" type="select" :label="__('styleguide.f_sport')" :placeholder="__('styleguide.f_choose')" required
                             :options="$sportCards->mapWithKeys(fn ($s) => [$s->id => $s->label()])->all()" />
                    <x-field name="demo_city" type="select" :label="__('styleguide.f_city')" :placeholder="__('styleguide.f_choose')" required
                             :options="\App\Models\City::orderBy('name')->pluck('name', 'id')->all()" />
                </x-step-form.step>
                <x-step-form.step :n="2">
                    <x-field name="demo_when" type="datetime-local" :label="__('styleguide.f_when')" required />
                    <x-field name="demo_where" :label="__('styleguide.f_where')" :placeholder="__('styleguide.f_where_ph')" required />
                </x-step-form.step>
                <x-step-form.step :n="3">
                    <x-field name="demo_players" type="number" :label="__('styleguide.f_players')" value="10" min="2" max="50" required />
                </x-step-form.step>
            </x-step-form>
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-icons">
        <h2 id="sg-icons" class="t-title2">{{ __('styleguide.icons') }}</h2>
        <p class="t-callout t-secondary">{{ __('styleguide.icons_note') }}</p>
        <div class="sg-icons">
            @foreach ($icons as $icon)
                <div><x-dynamic-component :component="'icon.'.$icon" /><code class="t-footnote t-secondary">{{ $icon }}</code></div>
            @endforeach
        </div>
    </section>
@endsection

@push('scripts')
<script>
    // Values are read from tokens.css at runtime, so this page can never drift from the tokens.
    (() => {
        const root = getComputedStyle(document.documentElement);
        const token = (name) => root.getPropertyValue(name).trim();
        // Resolve var() chains (e.g. --on-sport-x: var(--text)) to a concrete color via a probe element.
        const probe = document.createElement('span');
        document.body.appendChild(probe);
        const resolve = (name) => { probe.style.color = `var(${name})`; return getComputedStyle(probe).color; };
        const rgb = (c) => c.match(/\d+(\.\d+)?/g).slice(0, 3).map(Number);
        const hex = (c) => '#' + rgb(c).map((v) => v.toString(16).padStart(2, '0')).join('').toUpperCase();
        const lum = (c) => { const [r, g, b] = rgb(c).map((v) => { v /= 255; return v <= .03928 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4; }); return .2126 * r + .7152 * g + .0722 * b; };
        const ratio = (a, b) => { const [x, y] = [lum(a), lum(b)].sort((p, q) => q - p); return (x + .05) / (y + .05); };
        const ok = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
        const bad = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>';

        document.querySelectorAll('[data-hex]').forEach((el) => {
            const v = token(el.dataset.hex);
            el.textContent = v.startsWith('#') || v.startsWith('rgb') ? hex(resolve(el.dataset.hex)) : v;
        });
        document.querySelectorAll('[data-contrast]').forEach((el) => {
            const [a, b] = el.dataset.contrast.split('|');
            const r = ratio(resolve(a), resolve(b));
            const pass = r >= 4.5;
            el.className = pass ? 'sg-ok' : 'sg-bad';
            el.innerHTML = `${pass ? ok : bad} ${r.toFixed(2)}:1${!pass && el.dataset.large && r >= 3 ? ' · ' + el.dataset.large : ''}`;
        });
        document.querySelectorAll('[data-db]').forEach((el) => {
            const same = hex(resolve(el.dataset.db)) === el.dataset.dbColor.toUpperCase();
            el.className = 't-footnote ' + (same ? 'sg-ok' : 'sg-bad');
            el.innerHTML = `${same ? ok : bad} ${same ? el.dataset.ok : el.dataset.bad + ': ' + el.dataset.dbColor}`;
        });
        probe.remove();
    })();
</script>
@endpush
