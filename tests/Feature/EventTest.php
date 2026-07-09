<?php

use App\Models\Event;
use App\Models\Sport;
use App\Models\User;

test('guests are redirected from the event create page', function () {
    $this->get(route('events.create'))->assertRedirect(route('login'));
});

test('an authenticated user can create an event', function () {
    $user = User::factory()->create();
    $sport = Sport::factory()->create();

    $response = $this->actingAs($user)->post(route('events.store'), [
        'sport_id' => $sport->id,
        'title' => 'Sunday 5-a-side',
        'start_time' => now()->addDay()->format('Y-m-d\TH:i'),
        'city' => 'Chișinău',
        'max_participants' => 10,
        'skill_level' => 3,
    ]);

    $event = Event::first();

    expect($event)->not->toBeNull();
    expect($event->user_id)->toBe($user->id);
    expect($event->title)->toBe('Sunday 5-a-side');
    $response->assertRedirect(route('events.show', $event));
});

test('event creation validates required fields and future start time', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('events.store'), [
            'title' => '',
            'start_time' => now()->subDay()->format('Y-m-d\TH:i'),
        ])
        ->assertSessionHasErrors(['sport_id', 'title', 'start_time']);

    expect(Event::count())->toBe(0);
});

test('the index only shows upcoming events', function () {
    $user = User::factory()->create();
    $upcoming = Event::factory()->create(['title' => 'Upcoming game']);
    $past = Event::factory()->past()->create(['title' => 'Old game']);

    $this->actingAs($user)
        ->get(route('events.index'))
        ->assertOk()
        ->assertSee('Upcoming game')
        ->assertDontSee('Old game');
});

test('the index filters by sport', function () {
    $user = User::factory()->create();
    $football = Sport::factory()->create(['name' => 'Football', 'slug' => 'football']);
    $tennis = Sport::factory()->create(['name' => 'Tennis', 'slug' => 'tennis']);

    Event::factory()->create(['sport_id' => $football->id, 'title' => 'Football match']);
    Event::factory()->create(['sport_id' => $tennis->id, 'title' => 'Tennis match']);

    $this->actingAs($user)
        ->get(route('events.index', ['sport' => $football->id]))
        ->assertOk()
        ->assertSee('Football match')
        ->assertDontSee('Tennis match');
});

test('the index filters by city', function () {
    $user = User::factory()->create();

    Event::factory()->create(['city' => 'Chișinău', 'title' => 'Capital game']);
    Event::factory()->create(['city' => 'Bălți', 'title' => 'Northern game']);

    $this->actingAs($user)
        ->get(route('events.index', ['city' => 'Chișinău']))
        ->assertOk()
        ->assertSee('Capital game')
        ->assertDontSee('Northern game');
});
