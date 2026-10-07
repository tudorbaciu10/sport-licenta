<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Room;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'sport_id' => Sport::factory(),
            'city_id' => City::factory(),
            'title' => fake()->randomElement(['Meci de seară', 'Joc amical', 'Antrenament deschis', 'Căutăm jucători', 'Meci de weekend']),
            'description' => fake()->optional()->sentence(12),
            'location_name' => fake()->streetName(),
            'venue_type' => fake()->randomElement(['indoor', 'outdoor']),
            'match_date_time' => fake()->dateTimeBetween('+2 hours', '+10 days')->setTime(fake()->numberBetween(8, 21), fake()->randomElement([0, 30])),
            'max_players' => fake()->randomElement([4, 6, 10, 12]),
            'current_players_count' => 0,
            'rules' => fake()->randomElements(['Fără tackling', 'Echipe mixte', 'Plata la teren', 'Vino cu 10 min mai devreme', 'Nivel mediu'], 2),
        ];
    }
}
