@extends('layouts.app')

@section('title', 'Creează cont')

@section('content')
    <div class="auth">
        <h1 class="display">Creează cont</h1>
        <p class="muted" style="margin-bottom:24px">Ai deja cont? <a href="{{ route('login') }}">Intră în cont</a>.</p>

        <form method="POST" action="{{ route('register') }}" class="panel form-stack">
            @csrf
            <div class="field">
                <label for="name">Nume</label>
                <input id="name" name="name" class="input" value="{{ old('name') }}" required autofocus autocomplete="name" maxlength="80">
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" class="input" value="{{ old('email') }}" required autocomplete="email">
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="password">Parolă, minim 8 caractere</label>
                <input id="password" type="password" name="password" class="input" required autocomplete="new-password">
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="password_confirmation">Repetă parola</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="input" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-ball">Creează cont</button>
        </form>
    </div>
@endsection
