@extends('layouts.app')

@section('title', __('legal.privacy.title'))

@section('content')
    <article class="legal">
        <header class="legal__head">
            <h1 class="t-large">{{ __('legal.privacy.title') }}</h1>
            <p class="t-footnote t-secondary">{{ __('legal.updated', ['date' => \Illuminate\Support\Carbon::parse('2026-10-08')->locale(app()->getLocale())->isoFormat('D MMMM YYYY')]) }}</p>
        </header>
        <p class="t-body measure">{{ __('legal.privacy.intro') }}</p>

        @foreach (__('legal.privacy.sections') as $i => [$heading, $text])
            <section aria-labelledby="privacy-{{ $i }}">
                <h2 id="privacy-{{ $i }}" class="t-title2">{{ $heading }}</h2>
                <p class="t-body measure">
                    {!! str_replace(
                        [':email', e(__('consent.cookie_policy'))],
                        ['<a href="mailto:'.e(config('app.contact_email')).'">'.e(config('app.contact_email')).'</a>', '<a href="'.route('legal.cookies').'">'.e(__('consent.cookie_policy')).'</a>'],
                        e($text)
                    ) !!}
                </p>
            </section>
        @endforeach

        <p class="t-callout t-secondary measure">{{ __('legal.privacy.legal_note') }}</p>
    </article>
@endsection
