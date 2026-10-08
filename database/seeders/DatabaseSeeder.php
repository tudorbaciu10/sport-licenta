<?php

namespace Database\Seeders;

use App\Enums\ParticipationStatus;
use App\Enums\RoomStatus;
use App\Models\City;
use App\Models\Room;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $cities = collect([
            ['Chișinău', 'chisinau'], ['Bălți', 'balti'], ['Cahul', 'cahul'],
            ['Orhei', 'orhei'], ['Ungheni', 'ungheni'], ['Comrat', 'comrat'], ['Soroca', 'soroca'],
        ])->map(fn ($c) => City::updateOrCreate(['slug' => $c[1]], ['name' => $c[0]]));

        // Colors come from Sport::COLORS, which mirrors the --sport-* design tokens.
        $sports = collect([
            ['Fotbal', 'fotbal'], ['Baschet', 'baschet'], ['Tenis', 'tenis'], ['Volei', 'volei'],
            ['Handbal', 'handbal'], ['Alergare', 'alergare'], ['Tenis de masă', 'tenis-de-masa'], ['Padel', 'padel'],
        ])->map(fn ($s) => Sport::updateOrCreate(['slug' => $s[1]], ['name' => $s[0], 'color' => Sport::COLORS[$s[1]]]));

        User::factory()->create(['name' => 'Demo', 'email' => 'demo@sport.md']);
        $players = User::factory(30)->create();

        // Most rooms in Chișinău so the demo city looks busy.
        foreach (range(1, 40) as $i) {
            $city = $i <= 24 ? $cities->first() : $cities->random();
            $room = Room::factory()->create([
                'user_id' => $players->random()->id,
                'sport_id' => $sports->random()->id,
                'city_id' => $city->id,
            ]);

            $joined = $players->except($room->user_id)->random(rand(0, $room->max_players - 1));
            $attach = [$room->user_id => ['status' => ParticipationStatus::Joined->value]]
                + $joined->mapWithKeys(fn ($u) => [$u->id => ['status' => ParticipationStatus::Joined->value]])->all();
            $room->participants()->attach($attach);

            $count = count($attach);
            $room->forceFill([
                'current_players_count' => $count,
                'status' => $count >= $room->max_players ? RoomStatus::Full : RoomStatus::Open,
            ])->save();

            $interested = $players->diff($joined)->except($room->user_id)->random(rand(0, 4));
            $room->participants()->attach($interested->pluck('id'), ['status' => ParticipationStatus::Interested->value]);
        }
    }
}
