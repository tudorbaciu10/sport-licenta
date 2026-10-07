@extends('layouts.app')

@section('title', $room->title)

@section('content')
    @php
        $players = $room->participants->where('pivot.status', 'joined');
        $interested = $room->participants->where('pivot.status', 'interested');
        $left = $room->spotsLeft();
        $isCreator = auth()->id() === $room->user_id;
        $initials = fn ($name) => mb_strtoupper(collect(explode(' ', $name))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->join(''));
    @endphp

    <a href="{{ route('rooms.index', ['sport' => $room->sport->slug, 'city' => $room->city->slug]) }}" class="muted">Înapoi la meciuri</a>

    <div class="detail" style="margin-top:16px; --c: {{ $room->sport->color }}">
        <section class="panel" style="border-top: 6px solid var(--c)">
            <span class="tag">{{ $room->sport->name }}</span>
            <h1 class="display">{{ $room->title }}</h1>
            <p class="muted">{{ $room->location_name }}, {{ $room->city->name }}@if ($room->venueLabel()), teren {{ mb_strtolower($room->venueLabel()) }}@endif</p>

            <dl class="facts">
                <div><dt>Când</dt><dd>{{ $room->match_date_time->locale('ro')->isoFormat('dddd, D MMM, HH:mm') }}</dd></div>
                <div><dt>Locuri</dt><dd>{{ $room->current_players_count }} din {{ $room->max_players }}</dd></div>
                <div><dt>Stare</dt><dd>{{ $room->status->label() }}</dd></div>
            </dl>

            <span class="dots" aria-hidden="true">
                @for ($i = 0; $i < $room->max_players; $i++)
                    <i class="{{ $i < $room->current_players_count ? '' : 'o' }}"></i>
                @endfor
            </span>

            @if ($room->description)
                <p style="margin-top:24px; white-space:pre-line">{{ $room->description }}</p>
            @endif

            @if (! empty($room->rules))
                <h2 class="display" style="font-size:1.8rem; margin-top:28px">Reguli</h2>
                <ul class="rules">
                    @foreach ($room->rules as $rule)
                        <li>{{ $rule }}</li>
                    @endforeach
                </ul>
            @endif
        </section>

        <aside class="panel side">
            <div class="actions" style="margin-top:0">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-ball">Intră în cont ca să participi</a>
                @else
                    @if ($myStatus?->value === 'joined')
                        <p>Ești în echipă. {{ $isCreator ? 'Tu organizezi meciul.' : '' }}</p>
                        @unless ($isCreator)
                            <form method="POST" action="{{ route('rooms.leave', $room) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-quiet" style="width:100%">Ies din meci</button>
                            </form>
                        @endunless
                    @else
                        <form method="POST" action="{{ route('rooms.join', $room) }}">
                            @csrf
                            <button class="btn btn-ball" style="width:100%" @disabled(! $room->isJoinable() || $left === 0)>
                                {{ $left === 0 ? 'Meci complet' : 'Ocupă un loc' }}
                            </button>
                        </form>
                        @if ($myStatus?->value === 'interested')
                            <form method="POST" action="{{ route('rooms.leave', $room) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-quiet" style="width:100%">Nu mă mai interesează</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('rooms.interest', $room) }}">
                                @csrf
                                <button class="btn btn-line" style="width:100%">Mă interesează</button>
                            </form>
                        @endif
                    @endif
                @endguest
            </div>

            <h2 class="display" style="margin-top:28px">Jucători ({{ $players->count() }})</h2>
            <ul class="people">
                @foreach ($players as $user)
                    <li>
                        <span class="avatar" style="{{ $user->id === $room->user_id ? 'background:var(--c);color:var(--ink)' : '' }}">{{ $initials($user->name) }}</span>
                        <span>{{ $user->name }} @if ($user->id === $room->user_id)<span class="muted">, organizator</span>@endif</span>
                    </li>
                @endforeach
            </ul>

            @if ($interested->isNotEmpty())
                <h2 class="display">Interesați ({{ $interested->count() }})</h2>
                <ul class="people">
                    @foreach ($interested as $user)
                        <li><span class="avatar">{{ $initials($user->name) }}</span><span class="muted">{{ $user->name }}</span></li>
                    @endforeach
                </ul>
            @endif
        </aside>
    </div>
@endsection
