<?php

use App\Models\Event;
use App\Models\User;

test('an AJAX room request returns the bare partial (no layout)', function () {
    $event = Event::factory()->create(['title' => 'Deep Link Match']);

    $res = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get(route('landing.room', $event));

    $res->assertOk()->assertSee('Deep Link Match');
    expect($res->getContent())->not->toContain('<html');
});

test('a direct room visit returns the full landing page with the panel pre-opened', function () {
    $event = Event::factory()->create(['title' => 'Shared Match']);

    $this->get(route('landing.room', $event))
        ->assertOk()
        ->assertSee('<html', false)          // full document
        ->assertSee('id="rooms-list"', false) // the grid is rendered behind
        ->assertSee('data-open-room', false)  // panel pre-opened
        ->assertSee('Shared Match');          // the event detail
});

test('the guest CTA carries a relative next back to the room', function () {
    $event = Event::factory()->create();

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get(route('landing.room', $event))
        ->assertSee('next=%2Frooms%2F'.$event->id, false);
});

test('logging in with next returns the user to the room', function () {
    $event = Event::factory()->create();
    $user = User::factory()->create();
    $target = "/rooms/{$event->id}";

    $this->get(route('login', ['next' => $target]));   // stores intended
    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect($target);
});

test('registering with next returns the user to the room', function () {
    $event = Event::factory()->create();
    $target = "/rooms/{$event->id}";

    $this->get(route('register', ['next' => $target]));
    $this->post('/register', [
        'name' => 'Newbie',
        'email' => 'newbie@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect($target);
});

test('an unsafe next value is ignored (no open redirect)', function () {
    $user = User::factory()->create();

    $this->get(route('login', ['next' => 'https://evil.example.com']));
    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard'));
});
