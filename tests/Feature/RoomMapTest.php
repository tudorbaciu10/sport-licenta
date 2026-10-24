<?php

use App\Enums\RoomStatus;
use App\Models\City;
use App\Models\Room;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

/** An upcoming open match in Chișinău with a pin, unless $attrs say otherwise. */
function mapRoom(array $attrs = []): Room
{
    return Room::factory()->create(array_merge([
        'user_id' => User::factory(),
        'sport_id' => Sport::firstOrCreate(['slug' => 'fotbal'], ['name' => 'Fotbal', 'color' => '#34C759'])->id,
        'city_id' => City::firstOrCreate(['slug' => 'chisinau'], ['name' => 'Chișinău'])->id,
        'title' => 'Meci',
        'match_date_time' => now()->addDay()->setTime(19, 0),
        'max_players' => 10,
        'price' => 0,
        'latitude' => 47.0105,
        'longitude' => 28.8638,
    ], $attrs));
}

it('returns only upcoming open matches with free spots and a pin, from every city', function () {
    mapRoom(['title' => 'Bun']);
    mapRoom(['title' => 'Bălți', 'city_id' => City::firstOrCreate(['slug' => 'balti'], ['name' => 'Bălți'])->id]);
    mapRoom(['title' => 'Plin'])->forceFill(['current_players_count' => 10, 'status' => RoomStatus::Full])->save();
    mapRoom(['title' => 'Plin dar deschis'])->forceFill(['current_players_count' => 10])->save();
    mapRoom(['title' => 'Anulat'])->forceFill(['status' => RoomStatus::Cancelled])->save();
    mapRoom(['title' => 'Trecut', 'match_date_time' => now()->subHour()]);
    mapRoom(['title' => 'Fără pin', 'latitude' => null, 'longitude' => null]);

    $titles = collect($this->getJson('/rooms/map?city=chisinau')->assertOk()->json('rooms'))->pluck('title');

    expect($titles->all())->toEqualCanonicalizing(['Bun', 'Bălți']);   // city is ignored: the map is the area
});

it('applies the list filters', function () {
    mapRoom(['title' => 'Interior', 'venue_type' => 'indoor']);
    mapRoom(['title' => 'Exterior', 'venue_type' => 'outdoor']);

    expect($this->getJson('/rooms/map?venue=indoor')->json('rooms.*.title'))->toBe(['Interior']);
});

it('sends only what the pin and card need', function () {
    mapRoom();

    $room = $this->getJson('/rooms/map')->assertOk()->json('rooms.0');

    expect(array_keys($room))->toEqualCanonicalizing(['id', 'url', 'title', 'sport', 'starts_at', 'day', 'time', 'location', 'city', 'price', 'spots_left', 'availability', 'lat', 'lng'])
        ->and(array_keys($room['sport']))->toEqualCanonicalizing(['name', 'slug', 'var', 'icon'])
        ->and($room['lat'])->toBe(47.0105)
        ->and($room['spots_left'])->toBe(10)
        ->and($room['availability'])->toBe('open');
});

it('returns an empty list, not an error, when nothing matches', function () {
    mapRoom();

    $this->getJson('/rooms/map?q=nimic-de-gasit')->assertOk()->assertExactJson(['rooms' => [], 'truncated' => false]);
});

it('rejects invalid filters', function () {
    $this->getJson('/rooms/map?venue=spatiu')->assertStatus(422);
});

it('is rate limited', function () {
    foreach (range(1, 60) as $i) {
        $this->getJson('/rooms/map')->assertOk();
    }
    $this->getJson('/rooms/map')->assertStatus(429);
});

it('formats day and time in the page language', function () {
    $sunday = now()->next(Carbon::SUNDAY)->setTime(10, 0);
    mapRoom(['match_date_time' => $sunday]);

    $room = $this->withSession(['locale' => 'ru'])->getJson('/rooms/map')->json('rooms.0');

    expect($room['time'])->toBe('10:00')
        ->and($room['day'])->toBe($sunday->copy()->locale('ru')->isoFormat('ddd, D MMM'));
});
