<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+3 weeks');

        return [
            'user_id' => User::factory(),
            'sport_id' => Sport::factory(),
            'venue_id' => null,
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'start_time' => $start,
            'end_time' => (clone $start)->modify('+2 hours'),
            'city' => fake()->city(),
            'max_participants' => fake()->numberBetween(4, 20),
            'skill_level' => fake()->numberBetween(1, 5),
            'status' => Event::STATUS_OPEN,
        ];
    }

    /**
     * An event in the past.
     */
    public function past(): static
    {
        return $this->state(fn () => [
            'start_time' => fake()->dateTimeBetween('-3 weeks', '-1 day'),
        ]);
    }

    /**
     * A full event.
     */
    public function full(): static
    {
        return $this->state(fn () => [
            'status' => Event::STATUS_FULL,
        ]);
    }
}
