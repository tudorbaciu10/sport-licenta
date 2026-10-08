<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('hides "Meciurile mele" and "Profil" from guests and offers login instead', function () {
    $html = $this->get('/rooms')->assertOk()->getContent();

    expect($html)
        ->not->toContain('href="'.route('my-matches').'"')
        ->not->toContain('href="'.route('profile').'"')
        ->not->toContain('Meciurile mele')
        ->toContain('href="'.route('login').'"')
        ->toContain('href="'.route('register').'"')
        ->toContain('>Intră</span>');   // bottom tab bar
});

it('shows the full navigation to logged-in users', function () {
    $html = $this->actingAs(User::factory()->create())->get('/rooms')->assertOk()->getContent();

    expect($html)
        ->toContain('href="'.route('my-matches').'"')
        ->toContain('href="'.route('profile').'"')
        ->toContain('Meciurile mele')
        ->not->toContain('>Intră</span>');
});
