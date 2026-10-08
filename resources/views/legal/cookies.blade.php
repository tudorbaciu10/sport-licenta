@extends('layouts.app')

@section('title', __('legal.cookies.title'))

@php
    // Real names and durations from the app config, so this page cannot drift from what is set.
    $rows = [
        ['name' => config('session.cookie'), 'key' => 'session', 'category' => 'necessary', 'args' => ['minutes' => config('session.lifetime')]],
        ['name' => 'XSRF-TOKEN', 'key' => 'xsrf', 'category' => 'necessary', 'args' => []],
        ['name' => 'remember_web_…', 'key' => 'remember', 'category' => 'necessary', 'args' => []],
        ['name' => \App\Support\Consent::COOKIE, 'key' => 'consent', 'category' => 'necessary', 'args' => ['days' => \App\Support\Consent::DAYS]],
        ['name' => \App\Support\Consent::LOCALE_COOKIE, 'key' => 'locale', 'category' => 'preferences', 'args' => []],
    ];
@endphp

@section('content')
    <article class="legal">
        <header class="legal__head">
            <h1 class="t-large">{{ __('legal.cookies.title') }}</h1>
            <p class="t-footnote t-secondary">{{ __('legal.updated', ['date' => \Illuminate\Support\Carbon::parse('2026-10-08')->locale(app()->getLocale())->isoFormat('D MMMM YYYY')]) }}</p>
        </header>
        <p class="t-body measure">{{ __('legal.cookies.intro') }}</p>

        <section aria-labelledby="cookie-table">
            <h2 id="cookie-table" class="t-title2">{{ __('legal.cookies.table_title') }}</h2>
            <div class="legal__table">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">{{ __('legal.cookies.col_name') }}</th>
                            <th scope="col">{{ __('legal.cookies.col_category') }}</th>
                            <th scope="col">{{ __('legal.cookies.col_purpose') }}</th>
                            <th scope="col">{{ __('legal.cookies.col_duration') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            @php([$purpose, $duration] = __('legal.cookies.rows.'.$row['key']))
                            <tr>
                                <td data-label="{{ __('legal.cookies.col_name') }}"><code>{{ $row['name'] }}</code></td>
                                <td data-label="{{ __('legal.cookies.col_category') }}">{{ __('legal.cookies.'.$row['category']) }}</td>
                                <td data-label="{{ __('legal.cookies.col_purpose') }}">{{ $purpose }}</td>
                                <td data-label="{{ __('legal.cookies.col_duration') }}">{{ trans($duration, $row['args']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section aria-labelledby="cookie-manage">
            <h2 id="cookie-manage" class="t-title2">{{ __('legal.cookies.manage_title') }}</h2>
            <p class="t-body measure">{{ __('legal.cookies.manage_text') }}</p>
            <p><button type="button" class="btn btn--secondary" onclick="window.dispatchEvent(new CustomEvent('open-cookie-settings'))">{{ __('consent.footer_settings') }}</button></p>
        </section>

        <p class="t-callout"><a href="{{ route('legal.privacy') }}">{{ __('consent.privacy_policy') }}</a></p>
    </article>
@endsection
