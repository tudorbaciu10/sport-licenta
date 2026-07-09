<?php

use App\Models\Event;
use App\Models\User;

test('a user can join an open event', function () {
    $organizer = User::factory()->create();
    $joiner = User::factory()->create();
    $event = Event::factory()->for($organizer, 'creator')->create(['max_participants' => 10]);

    $this->actingAs($joiner)
        ->post(route('events.join', $event))
        ->assertRedirect();

    expect($event->fresh()->hasParticipant($joiner))->toBeTrue();
});

test('a user cannot join the same event twice', function () {
    $event = Event::factory()->create(['max_participants' => 10]);
    $joiner = User::factory()->create();

    $this->actingAs($joiner)->post(route('events.join', $event));
    $this->actingAs($joiner)->post(route('events.join', $event));

    expect($event->participants()->count())->toBe(1);
});

test('the organizer cannot join their own event', function () {
    $organizer = User::factory()->create();
    $event = Event::factory()->for($organizer, 'creator')->create();

    $this->actingAs($organizer)
        ->post(route('events.join', $event))
        ->assertSessionHas('error');

    expect($event->participants()->count())->toBe(0);
});

test('a user cannot join a full event and status flips to full', function () {
    $event = Event::factory()->create(['max_participants' => 1]);
    $first = User::factory()->create();
    $second = User::factory()->create();

    $this->actingAs($first)->post(route('events.join', $event));
    expect($event->fresh()->status)->toBe(Event::STATUS_FULL);

    $this->actingAs($second)
        ->post(route('events.join', $event))
        ->assertSessionHas('error');

    expect($event->participants()->count())->toBe(1);
});

test('leaving a full event reopens it', function () {
    $event = Event::factory()->create(['max_participants' => 1]);
    $joiner = User::factory()->create();

    $this->actingAs($joiner)->post(route('events.join', $event));
    expect($event->fresh()->status)->toBe(Event::STATUS_FULL);

    $this->actingAs($joiner)
        ->delete(route('events.leave', $event))
        ->assertRedirect();

    expect($event->fresh()->status)->toBe(Event::STATUS_OPEN);
    expect($event->participants()->count())->toBe(0);
});
