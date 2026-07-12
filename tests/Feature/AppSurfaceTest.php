<?php

use App\Models\Event;
use App\Models\Sport;
use App\Models\User;

/** AJAX (slide-over) request helper — sets the header our controllers branch on. */
function ajax($test)
{
    return $test->withHeaders(['X-Requested-With' => 'XMLHttpRequest']);
}

test('the event detail partial is public and shows a login CTA to guests', function () {
    $event = Event::factory()->create();

    $this->get(route('landing.room', $event))
        ->assertOk()
        ->assertSee(route('login'))
        ->assertDontSee(route('events.join', $event));
});

test('a non-participant sees the join action, a participant sees leave', function () {
    $event = Event::factory()->create(['max_participants' => 10]);
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('landing.room', $event))
        ->assertOk()
        ->assertSee(route('events.join', $event));

    $event->participants()->attach($user->id, ['status' => 'joined']);

    $this->actingAs($user)->get(route('landing.room', $event))
        ->assertOk()
        ->assertSee(route('events.leave', $event));
});

test('the creator sees an organizer note, not join/leave', function () {
    $creator = User::factory()->create();
    $event = Event::factory()->for($creator, 'creator')->create();

    $this->actingAs($creator)->get(route('landing.room', $event))
        ->assertOk()
        ->assertDontSee(route('events.join', $event))
        ->assertDontSee(route('events.leave', $event));
});

test('an AJAX join returns the detail partial now showing leave and increments the count', function () {
    $event = Event::factory()->create(['max_participants' => 10]);
    $user = User::factory()->create();

    ajax($this->actingAs($user))
        ->post(route('events.join', $event))
        ->assertOk()
        ->assertSee(route('events.leave', $event));

    expect($event->participants()->count())->toBe(1);
});

test('AJAX join still enforces the full guard', function () {
    $event = Event::factory()->create(['max_participants' => 1]);
    [$a, $b] = User::factory()->count(2)->create();

    ajax($this->actingAs($a))->post(route('events.join', $event))->assertOk();
    ajax($this->actingAs($b))->post(route('events.join', $event))->assertOk();

    expect($event->participants()->count())->toBe(1);
});

test('the create-event and edit-profile panels require auth', function () {
    $this->get(route('events.create-form'))->assertRedirect(route('login'));
    $this->get(route('profile.details.form'))->assertRedirect(route('login'));

    $user = User::factory()->create();
    $this->actingAs($user)->get(route('events.create-form'))->assertOk();
    $this->actingAs($user)->get(route('profile.details.form'))->assertOk();
});

test('an AJAX event create returns a success marker and persists the event', function () {
    $user = User::factory()->create();
    $sport = Sport::factory()->create();

    ajax($this->actingAs($user))
        ->post(route('events.store'), [
            'sport_id' => $sport->id,
            'title' => 'Panel Match',
            'start_time' => now()->addDay()->format('Y-m-d\TH:i'),
            'max_participants' => 8,
        ])
        ->assertOk()
        ->assertSee('data-success', false);

    expect(Event::where('title', 'Panel Match')->exists())->toBeTrue();
});

test('an AJAX event create with bad data returns 422 with the form errors', function () {
    $user = User::factory()->create();

    ajax($this->actingAs($user))
        ->post(route('events.store'), ['title' => '', 'start_time' => now()->subDay()->format('Y-m-d\TH:i')])
        ->assertStatus(422)
        ->assertSee('panel-error', false);

    expect(Event::count())->toBe(0);
});

test('an AJAX profile save returns a success marker and persists', function () {
    $user = User::factory()->create();

    ajax($this->actingAs($user))
        ->patch(route('profile.details.update'), [
            'bio' => 'Panel bio',
            'city' => 'Chișinău',
            'skill_level' => 4,
        ])
        ->assertOk()
        ->assertSee('data-success', false);

    expect($user->fresh()->profile->bio)->toBe('Panel bio');
});
