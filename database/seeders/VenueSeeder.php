<?php

namespace Database\Seeders;

use App\Models\Venue;
use App\Models\VenueCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VenueSeeder extends Seeder
{
    public function run(): void
    {
        $bySlug = VenueCategory::pluck('id', 'slug');

        $venues = [
            ['name' => 'Arena Chișinău', 'city' => 'Chișinău', 'capacity' => 120, 'slug' => 'pitches', 'surface' => 'synthetic', 'price' => 400],
            ['name' => 'Sport Complex Dinamo', 'city' => 'Chișinău', 'capacity' => 60, 'slug' => 'halls-gyms', 'surface' => null, 'price' => 250],
            ['name' => 'Padel Club Central', 'city' => 'Bălți', 'capacity' => 24, 'slug' => 'racket-courts', 'surface' => 'hard', 'price' => 200],
            ['name' => 'City Tennis Courts', 'city' => 'Chișinău', 'capacity' => 16, 'slug' => 'racket-courts', 'surface' => 'grass', 'price' => 180],
        ];

        foreach ($venues as $venue) {
            Venue::firstOrCreate(
                ['name' => $venue['name']],
                [
                    'slug' => Str::slug($venue['name']),
                    'city' => $venue['city'],
                    'country' => 'Moldova',
                    'capacity' => $venue['capacity'],
                    'venue_category_id' => $bySlug[$venue['slug']] ?? null,
                    'surface' => $venue['surface'],
                    'price_per_hour' => $venue['price'],
                    'currency' => 'MDL',
                    'source' => 'user',
                    'is_published' => true,
                ],
            );
        }
    }
}
