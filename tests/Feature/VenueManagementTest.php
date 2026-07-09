<?php

use App\Models\User;
use App\Models\Venue;
use App\Models\VenueCategory;

test('guests are redirected from the create facility page', function () {
    $this->get(route('venues.create'))->assertRedirect(route('login'));
});

test('an authenticated user can list a facility and it is published instantly', function () {
    $user = User::factory()->create();
    $category = VenueCategory::factory()->create();

    $response = $this->actingAs($user)->post(route('venues.store'), [
        'name' => 'My Synthetic Pitch',
        'venue_category_id' => $category->id,
        'city' => 'Chișinău',
        'country' => 'Moldova',
        'surface' => 'synthetic',
        'price_per_hour' => 350,
    ]);

    $venue = Venue::first();

    expect($venue)->not->toBeNull();
    expect($venue->user_id)->toBe($user->id);
    expect($venue->is_published)->toBeTrue();
    $response->assertRedirect(route('venues.show', $venue));
});

test('a non-owner cannot edit or delete a facility', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $venue = Venue::factory()->for($owner, 'owner')->create();

    $this->actingAs($intruder)->get(route('venues.edit', $venue))->assertForbidden();
    $this->actingAs($intruder)->patch(route('venues.update', $venue), [
        'name' => 'Hacked', 'venue_category_id' => $venue->venue_category_id, 'city' => 'X',
    ])->assertForbidden();
    $this->actingAs($intruder)->delete(route('venues.destroy', $venue))->assertForbidden();

    expect($venue->fresh()->name)->not->toBe('Hacked');
});

test('the owner can update and delete their facility', function () {
    $owner = User::factory()->create();
    $venue = Venue::factory()->for($owner, 'owner')->create();

    $this->actingAs($owner)->patch(route('venues.update', $venue), [
        'name' => 'Renamed Arena',
        'venue_category_id' => $venue->venue_category_id,
        'city' => 'Bălți',
    ])->assertRedirect(route('venues.show', $venue));

    expect($venue->fresh()->name)->toBe('Renamed Arena');

    $this->actingAs($owner)->delete(route('venues.destroy', $venue))->assertRedirect(route('venues.mine'));
    expect(Venue::find($venue->id))->toBeNull();
});

test('an admin can edit any facility', function () {
    $admin = User::factory()->admin()->create();
    $venue = Venue::factory()->create();

    $this->actingAs($admin)->get(route('venues.edit', $venue))->assertOk();
});
