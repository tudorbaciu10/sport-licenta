<?php

namespace Database\Factories;

use App\Models\Venue;
use App\Models\VenueCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    protected $model = Venue::class;

    public function definition(): array
    {
        $name = fake()->company().' Arena';

        return [
            'user_id' => null,
            'venue_category_id' => VenueCategory::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'description' => fake()->sentence(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'country' => 'Moldova',
            'locality' => null,
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'capacity' => fake()->numberBetween(10, 200),
            'surface' => fake()->randomElement([null, 'synthetic', 'grass', 'hard', 'parquet']),
            'is_indoor' => fake()->boolean(),
            'price_per_hour' => fake()->randomElement([null, 150, 200, 250, 300, 400]),
            'currency' => 'MDL',
            'contact_phone' => fake()->numerify('+373 ## ### ###'),
            'contact_email' => fake()->safeEmail(),
            'photo_path' => null,
            'source' => 'user',
            'external_id' => null,
            'is_published' => true,
        ];
    }

    /**
     * A synthetic-surface football pitch.
     */
    public function pitch(): static
    {
        return $this->state(fn () => [
            'surface' => 'synthetic',
            'is_indoor' => false,
        ]);
    }

    /**
     * A venue with no registered owner (e.g. imported / seeded).
     */
    public function unowned(): static
    {
        return $this->state(fn () => ['user_id' => null]);
    }

    /**
     * A hidden (unpublished) venue.
     */
    public function unpublished(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }
}
