<?php

use App\Models\Venue;
use App\Models\VenueCategory;

test('the landing page loads with the facilities section', function () {
    $this->get(route('landing'))
        ->assertOk()
        ->assertSee('venues-list', false);
});

test('the facilities partial lists only published venues', function () {
    $published = Venue::factory()->create(['name' => 'Open Arena']);
    $hidden = Venue::factory()->unpublished()->create(['name' => 'Hidden Arena']);

    $this->get(route('landing.venues'))
        ->assertOk()
        ->assertSee('Open Arena')
        ->assertDontSee('Hidden Arena');
});

test('facilities filter by category', function () {
    $pitches = VenueCategory::factory()->create();
    $courts = VenueCategory::factory()->create();

    Venue::factory()->for($pitches, 'category')->create(['name' => 'Green Pitch']);
    Venue::factory()->for($courts, 'category')->create(['name' => 'Ace Court']);

    $this->get(route('landing.venues', ['category' => $pitches->id]))
        ->assertOk()
        ->assertSee('Green Pitch')
        ->assertDontSee('Ace Court');
});

test('facilities filter by city and surface', function () {
    Venue::factory()->create(['name' => 'Chisinau Synthetic', 'city' => 'Chișinău', 'surface' => 'synthetic']);
    Venue::factory()->create(['name' => 'Balti Grass', 'city' => 'Bălți', 'surface' => 'grass']);

    $this->get(route('landing.venues', ['venue_city' => 'Chișinău']))
        ->assertOk()
        ->assertSee('Chisinau Synthetic')
        ->assertDontSee('Balti Grass');

    $this->get(route('landing.venues', ['surface' => 'grass']))
        ->assertOk()
        ->assertSee('Balti Grass')
        ->assertDontSee('Chisinau Synthetic');
});

test('a published facility has a public page', function () {
    $venue = Venue::factory()->create(['name' => 'Public Arena']);

    $this->get(route('venues.show', $venue))
        ->assertOk()
        ->assertSee('Public Arena');
});

test('an unpublished facility 404s for guests', function () {
    $venue = Venue::factory()->unpublished()->create();

    $this->get(route('venues.show', $venue))->assertNotFound();
});
