<?php

namespace Database\Seeders;

use App\Models\VenueCategory;
use Illuminate\Database\Seeder;

class VenueCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Football / sports pitches', 'slug' => 'pitches', 'icon' => '🥅'],
            ['name' => 'Sports halls & gyms', 'slug' => 'halls-gyms', 'icon' => '🏋️'],
            ['name' => 'Racket courts', 'slug' => 'racket-courts', 'icon' => '🎾'],
            ['name' => 'Computer / gaming rooms', 'slug' => 'gaming-rooms', 'icon' => '🖥️'],
            ['name' => 'Other', 'slug' => 'other', 'icon' => '🏟️'],
        ];

        foreach ($categories as $category) {
            VenueCategory::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
