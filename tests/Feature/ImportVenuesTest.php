<?php

use App\Models\Venue;
use App\Models\VenueCategory;
use Illuminate\Support\Facades\Http;

function fakeOverpass(): void
{
    Http::fake([
        'overpass-api.de/*' => Http::response([
            'elements' => [
                ['type' => 'node', 'id' => 1, 'lat' => 47.0, 'lon' => 28.8, 'tags' => [
                    'name' => 'Test Synthetic Pitch', 'leisure' => 'pitch', 'sport' => 'soccer', 'surface' => 'synthetic',
                ]],
                ['type' => 'way', 'id' => 2, 'center' => ['lat' => 47.1, 'lon' => 28.9], 'tags' => [
                    'name' => 'Test Fitness Centre', 'leisure' => 'fitness_centre',
                ]],
                // Unnamed element — should be skipped.
                ['type' => 'node', 'id' => 3, 'lat' => 47.2, 'lon' => 28.7, 'tags' => ['leisure' => 'pitch']],
            ],
        ]),
    ]);
}

test('the OSM importer upserts named facilities and skips unnamed ones', function () {
    VenueCategory::factory()->create(['slug' => 'pitches', 'name' => 'Pitches']);
    VenueCategory::factory()->create(['slug' => 'halls-gyms', 'name' => 'Halls']);
    fakeOverpass();

    $this->artisan('venues:import', ['city' => 'Chișinău'])->assertSuccessful();

    expect(Venue::where('source', 'osm')->count())->toBe(2);
    $this->assertDatabaseHas('venues', ['name' => 'Test Synthetic Pitch', 'source' => 'osm', 'city' => 'Chișinău']);
    $this->assertDatabaseMissing('venues', ['external_id' => 'node/3']);
});

test('re-running the OSM import does not create duplicates', function () {
    VenueCategory::factory()->create(['slug' => 'pitches', 'name' => 'Pitches']);
    VenueCategory::factory()->create(['slug' => 'halls-gyms', 'name' => 'Halls']);
    fakeOverpass();

    $this->artisan('venues:import', ['city' => 'Chișinău']);
    $this->artisan('venues:import', ['city' => 'Chișinău']);

    expect(Venue::where('source', 'osm')->count())->toBe(2);
});

test('the google source is disabled without an api key', function () {
    $this->artisan('venues:import', ['city' => 'Chișinău', '--source' => 'google'])
        ->assertFailed();
});
