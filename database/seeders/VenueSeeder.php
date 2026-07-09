<?php

namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    public function run(): void
    {
        $venues = [
            ['name' => 'Arena Chișinău', 'city' => 'Chișinău', 'capacity' => 120],
            ['name' => 'Sport Complex Dinamo', 'city' => 'Chișinău', 'capacity' => 60],
            ['name' => 'Padel Club Central', 'city' => 'Bălți', 'capacity' => 24],
            ['name' => 'City Tennis Courts', 'city' => 'Chișinău', 'capacity' => 16],
        ];

        foreach ($venues as $venue) {
            Venue::firstOrCreate(
                ['name' => $venue['name']],
                $venue,
            );
        }
    }
}
