@extends('layouts.app')

@section('title', __('profile.title'))

@section('content')
<div class="profile">
    <header class="profile-head">
        <x-avatar :user="$user" size="xl" />
        <div class="profile-head__text">
            <h1 class="t-title1">{{ $user->name }}</h1>
            <p class="t-callout t-secondary profile-head__city">
                <x-icon.map-pin class="icon icon--xs" /> {{ $user->city?->label() ?? __('profile.no_city') }}
            </p>
        </div>
        <x-button variant="secondary" size="sm" :href="route('profile.edit')">{{ __('profile.edit') }}</x-button>
    </header>

    <dl class="stat-row">
        @foreach (['upcoming', 'played', 'organized'] as $key)
            <div class="stat">
                <dt class="t-footnote t-secondary">{{ __('profile.stats.'.$key) }}</dt>
                <dd class="t-title1 tabular">{{ $stats[$key] }}</dd>
            </div>
        @endforeach
    </dl>

    <section class="profile-section" aria-labelledby="my-sports">
        <h2 id="my-sports" class="t-title2">{{ __('profile.sports') }}</h2>
        @if ($user->sports->isEmpty())
            <x-empty-state icon="circle-plus" :title="__('profile.no_sports')">
                <x-button :href="route('profile.edit')">{{ __('profile.add_sports') }}</x-button>
            </x-empty-state>
        @else
            <ul class="list-group" role="list">
                @foreach ($user->sports->sortBy(fn ($s) => $s->label()) as $sport)
                    <li class="list-row">
                        <x-sport-icon :sport="$sport" size="md" />
                        <span class="list-row__text">
                            <span class="t-headline">{{ $sport->label() }}</span>
                            <span class="t-footnote t-secondary">
                                {{ __('profile.levels.'.$sport->pivot->level) }} · {{ __('profile.level_desc.'.$sport->pivot->level) }}@if ($sport->pivot->position) · {{ $sport->pivot->position }}@endif
                            </span>
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="profile-section">
        <ul class="list-group" role="list">
            <li><a class="list-row list-row--link" href="{{ route('my-matches') }}">
                <x-icon.calendar-days /> <span class="list-row__text t-body">{{ __('profile.my_matches') }}</span> <x-icon.chevron-right class="icon list-row__chevron" />
            </a></li>
            <li><a class="list-row list-row--link" href="{{ route('profile.edit') }}">
                <x-icon.user-round /> <span class="list-row__text t-body">{{ __('profile.edit') }}</span> <x-icon.chevron-right class="icon list-row__chevron" />
            </a></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="list-row list-row--link list-row--danger">
                        <x-icon.arrow-left /> <span class="list-row__text t-body">{{ __('profile.logout') }}</span>
                    </button>
                </form>
            </li>
        </ul>
    </section>
</div>
@endsection
