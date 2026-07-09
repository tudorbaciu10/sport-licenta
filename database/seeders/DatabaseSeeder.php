<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SportSeeder::class,
            VenueCategorySeeder::class,
            VenueSeeder::class,
        ]);

        $sports = Sport::all();

        // A platform administrator.
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
        ]);

        // A known test account with a profile and a couple of sports.
        $test = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $test->profile()->create([
            'bio' => 'Loves weekend football and tennis.',
            'city' => 'Chișinău',
            'skill_level' => 3,
        ]);
        $test->sports()->attach(
            $sports->whereIn('slug', ['football', 'tennis'])->pluck('id'),
            ['skill_level' => 3],
        );

        // A handful of other players.
        $players = User::factory(8)
            ->has(\App\Models\UserProfile::factory(), 'profile')
            ->create();

        $players->each(function (User $player) use ($sports) {
            $player->sports()->attach(
                $sports->random(rand(1, 3))->pluck('id'),
                ['skill_level' => rand(1, 5)],
            );
        });

        // Owner-listed rentable facilities across Chișinău / Bălți.
        $categories = VenueCategory::all();
        $owners = $players->concat([$test]);

        Venue::factory(8)
            ->recycle($categories)
            ->recycle($owners)
            ->create([
                'country' => 'Moldova',
            ])
            ->each(function (Venue $venue) use ($owners) {
                $venue->update([
                    'user_id' => $owners->random()->id,
                    'city' => fake()->randomElement(['Chișinău', 'Bălți']),
                ]);
            });

        // Demo events created by random players, some with participants.
        $organizers = $players->push($test);
        $venues = Venue::all();

        Event::factory(12)
            ->recycle($organizers)
            ->recycle($sports)
            ->recycle($venues)
            ->make()
            ->each(function (Event $event) use ($organizers, $sports, $venues, $players) {
                $event->user_id = $organizers->random()->id;
                $event->sport_id = $sports->random()->id;
                $event->venue_id = $venues->random()->id;
                $event->city = $venues->firstWhere('id', $event->venue_id)->city;
                $event->save();

                // Attach a few participants (excluding the organizer).
                $joiners = $players->where('id', '!=', $event->user_id)->random(rand(0, 3));
                foreach ($joiners as $joiner) {
                    $event->participants()->syncWithoutDetaching([
                        $joiner->id => ['status' => 'joined'],
                    ]);
                }
            });
    }
}
