<?php

namespace Database\Factories;

use App\Models\VenueCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<VenueCategory>
 */
class VenueCategoryFactory extends Factory
{
    protected $model = VenueCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'icon' => null,
        ];
    }
}
