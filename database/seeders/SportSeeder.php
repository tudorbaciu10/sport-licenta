<?php

namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SportSeeder extends Seeder
{
    public function run(): void
    {
        $sports = [
            'Football',
            'Basketball',
            'Tennis',
            'Volleyball',
            'Padel',
            'Running',
            'Cycling',
            'Table Tennis',
            'Badminton',
            'Handball',
        ];

        foreach ($sports as $name) {
            Sport::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
