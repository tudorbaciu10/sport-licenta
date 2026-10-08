@extends('layouts.app')

@section('title', __('auth.register_title'))

@section('content')
    <div class="auth-card">
        <header class="auth-card__head">
            <h1 class="t-large">{{ __('auth.register_title') }}</h1>
            <p class="t-callout t-secondary">{{ __('auth.register_intro') }}</p>
        </header>

        <form method="POST" action="{{ route('register') }}" class="auth-card__form" novalidate>
            @csrf
            <x-field name="name" :label="__('auth.name')" :placeholder="__('auth.name_ph')" autocomplete="name" maxlength="80" required autofocus />
            <x-field name="email" type="email" :label="__('auth.email')" autocomplete="email" inputmode="email"
                     autocapitalize="none" spellcheck="false" required />
            <x-field name="password" type="password" :label="__('auth.password')" :hint="__('auth.password_hint')"
                     autocomplete="new-password" minlength="8" required />
            <x-field name="password_confirmation" type="password" :label="__('auth.password_confirm')" autocomplete="new-password" required />
            <x-button type="submit" block>{{ __('auth.register') }}</x-button>
        </form>

        <p class="auth-card__switch t-callout t-secondary">
            {{ __('auth.have_account') }} <a href="{{ route('login') }}">{{ __('auth.login_link') }}</a>
        </p>
    </div>
@endsection
