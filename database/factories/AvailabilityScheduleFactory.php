<?php

namespace Database\Factories;

use App\Models\AvailabilitySchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AvailabilitySchedule>
 */
class AvailabilityScheduleFactory extends Factory
{
    protected $model = AvailabilitySchedule::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'start_time' => '18:00',
            'end_time' => '20:00',
        ];
    }
}
