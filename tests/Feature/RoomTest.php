<?php

use App\Enums\RoomStatus;
use App\Models\City;
use App\Models\Room;
use App\Models\Sport;
use App\Models\User;
use App\Services\RoomMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeRoom(array $attrs = []): Room
{
    return app(RoomMembership::class)->create(User::factory()->create(), array_merge([
        'sport_id' => Sport::factory()->create()->id,
        'city_id' => City::factory()->create()->id,
        'title' => 'Meci de test',
        'location_name' => 'Teren 1',
        'match_date_time' => now()->addDay(),
        'max_players' => 2,
    ], $attrs));
}

it('lists upcoming rooms filtered by city and sport', function () {
    $room = makeRoom();
    $other = makeRoom();

    $this->get(route('rooms.index', ['city' => $room->city->slug, 'sport' => $room->sport->slug]))
        ->assertOk()
        ->assertSee($room->city->name)
        ->assertDontSee($other->location_name.', '.$other->city->name);
});

it('lets an authenticated user create a room and joins them as first player', function () {
    $user = User::factory()->create();
    $sport = Sport::factory()->create();
    $city = City::factory()->create();

    $this->actingAs($user)->post(route('rooms.store'), [
        'sport_id' => $sport->id,
        'city_id' => $city->id,
        'title' => 'Fotbal joi',
        'location_name' => 'Botanica',
        'match_date_time' => now()->addDays(2)->format('Y-m-d\TH:i'),
        'max_players' => 10,
        'rules' => ['Fără tackling', '  ', ''],
    ])->assertRedirect();

    $room = Room::first();
    expect($room->user_id)->toBe($user->id)
        ->and($room->current_players_count)->toBe(1)
        ->and($room->rules)->toBe(['Fără tackling'])
        ->and($room->players()->whereKey($user->id)->exists())->toBeTrue();
});

it('rejects rooms in the past and guests', function () {
    $this->post(route('rooms.store'), [])->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->post(route('rooms.store'), ['match_date_time' => now()->subDay()->toDateTimeString()])
        ->assertSessionHasErrors(['sport_id', 'city_id', 'title', 'location_name', 'match_date_time', 'max_players']);
});

it('fills the room and refuses players beyond the limit', function () {
    $room = makeRoom(['max_players' => 2]);

    $this->actingAs(User::factory()->create())->post(route('rooms.join', $room))->assertSessionHasNoErrors();
    expect($room->fresh()->status)->toBe(RoomStatus::Full)
        ->and($room->fresh()->current_players_count)->toBe(2);

    $this->actingAs(User::factory()->create())->post(route('rooms.join', $room))->assertSessionHasErrors('room');
    expect($room->fresh()->current_players_count)->toBe(2);
});

it('records interest without taking a spot, and leaving reopens a full room', function () {
    $room = makeRoom(['max_players' => 2]);
    $fan = User::factory()->create();
    $player = User::factory()->create();

    $this->actingAs($fan)->post(route('rooms.interest', $room));
    expect($room->interested()->whereKey($fan->id)->exists())->toBeTrue()
        ->and($room->fresh()->current_players_count)->toBe(1);

    $this->actingAs($player)->post(route('rooms.join', $room));
    $this->actingAs($player)->delete(route('rooms.leave', $room));

    expect($room->fresh()->status)->toBe(RoomStatus::Open)
        ->and($room->fresh()->current_players_count)->toBe(1);
});

it('lets an interested user upgrade to joined', function () {
    $room = makeRoom(['max_players' => 3]);
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('rooms.interest', $room));
    $this->actingAs($user)->post(route('rooms.join', $room));

    expect($room->players()->whereKey($user->id)->exists())->toBeTrue()
        ->and($room->fresh()->current_players_count)->toBe(2);
});

it('filters by venue type, free spots, start time and free text', function () {
    $indoor = makeRoom(['venue_type' => 'indoor', 'title' => 'Volei de seară', 'match_date_time' => now()->addDay()->setTime(20, 0)]);
    $outdoor = makeRoom(['venue_type' => 'outdoor', 'title' => 'Fotbal dimineața', 'match_date_time' => now()->addDay()->setTime(9, 0), 'max_players' => 2]);
    $this->actingAs(User::factory()->create())->post(route('rooms.join', $outdoor)); // fills it

    $titles = fn (array $query) => $this->get(route('rooms.index', $query))->viewData('rooms')->pluck('title')->all();

    expect($titles(['venue' => 'indoor']))->toBe(['Volei de seară'])
        ->and($titles(['venue' => 'outdoor']))->toBe(['Fotbal dimineața'])
        ->and($titles(['free' => 1]))->toBe(['Volei de seară'])
        ->and($titles(['time' => '18:00']))->toBe(['Volei de seară'])
        ->and($titles(['q' => 'dimineața']))->toBe(['Fotbal dimineața']);

    $this->get(route('rooms.index', ['venue' => 'roof', 'time' => '25:99']))->assertSessionHasErrors(['venue', 'time']);
});

it('does not let the organiser leave their own room', function () {
    $room = makeRoom();

    $this->actingAs($room->creator)->delete(route('rooms.leave', $room))->assertSessionHasErrors('room');
});
