@extends('layouts.app')

@section('title', 'Intră în cont')

@section('content')
    <div class="auth">
        <h1 class="display">Intră în cont</h1>
        <p class="muted" style="margin-bottom:24px">Nu ai cont? <a href="{{ route('register') }}">Creează unul gratuit</a>.</p>

        <form method="POST" action="{{ route('login') }}" class="panel form-stack">
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" class="input" value="{{ old('email') }}" required autofocus autocomplete="email">
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="password">Parolă</label>
                <input id="password" type="password" name="password" class="input" required autocomplete="current-password">
            </div>
            <label class="check"><input type="checkbox" name="remember"> Ține-mă minte</label>
            <button type="submit" class="btn btn-ball">Intră în cont</button>
        </form>
    </div>
@endsection
