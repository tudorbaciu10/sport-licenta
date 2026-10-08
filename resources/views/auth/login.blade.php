@extends('layouts.app')

@section('title', __('auth.login_title'))

@section('content')
    <div class="auth-card">
        <header class="auth-card__head">
            <span class="auth-card__mark" aria-hidden="true"><x-icon.user-round /></span>
            <h1 class="t-large">{{ __('auth.login_title') }}</h1>
            <p class="t-callout t-secondary">{{ __('auth.login_intro') }}</p>
        </header>

        <form method="POST" action="{{ route('login') }}" class="auth-card__form" novalidate>
            @csrf
            <x-field name="email" type="email" :label="__('auth.email')" autocomplete="email" inputmode="email"
                     autocapitalize="none" spellcheck="false" required autofocus />
            <x-field name="password" type="password" :label="__('auth.password')" autocomplete="current-password" required />
            <label class="check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> {{ __('auth.remember') }}</label>
            <x-button type="submit" block>{{ __('auth.login') }}</x-button>
        </form>

        <p class="auth-card__switch t-callout t-secondary">
            {{ __('auth.no_account') }} <a href="{{ route('register') }}">{{ __('auth.register_link') }}</a>
        </p>
    </div>
@endsection
