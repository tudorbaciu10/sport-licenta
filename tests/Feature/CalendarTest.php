<?php

use App\Enums\RoomStatus;
use App\Models\City;
use App\Models\Room;
use App\Models\Sport;
use App\Models\User;
use App\Services\RoomMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Fixed "now": Wednesday 14 Oct 2026, 10:00 (Europe/Chisinau)
    Carbon::setTestNow(Carbon::parse('2026-10-14 10:00'));
});

afterEach(fn () => Carbon::setTestNow());

function calRoom(string $when, array $attrs = [], string $city = 'chisinau', string $sport = 'fotbal', ?User $creator = null): Room
{
    $names = ['chisinau' => 'Chișinău', 'balti' => 'Bălți', 'fotbal' => 'Fotbal', 'tenis' => 'Tenis', 'baschet' => 'Baschet'];

    return app(RoomMembership::class)->create($creator ?? User::factory()->create(), array_merge([
        'sport_id' => Sport::firstOrCreate(['slug' => $sport], ['name' => $names[$sport], 'color' => Sport::COLORS[$sport]])->id,
        'city_id' => City::firstOrCreate(['slug' => $city], ['name' => $names[$city]])->id,
        'title' => "Meci $when",
        'location_name' => 'Botanica',
        'match_date_time' => Carbon::parse($when),
        'max_players' => 10,
    ], $attrs));
}

// ---------- GET /calendar/days ----------

it('returns only upcoming open/full matches of the requested city, grouped by day', function () {
    calRoom('2026-10-14 09:00');                                   // earlier today: already started
    calRoom('2026-10-14 19:00');                                   // today, later
    calRoom('2026-10-16 18:00', sport: 'tenis');
    calRoom('2026-10-16 20:00');
    calRoom('2026-10-16 21:00', sport: 'tenis');
    calRoom('2026-10-20 18:00', city: 'balti');                    // other city
    calRoom('2026-10-22 18:00')->forceFill(['status' => RoomStatus::Cancelled])->save();
    calRoom('2026-10-23 18:00')->forceFill(['status' => RoomStatus::Finished])->save();
    calRoom('2026-11-02 18:00');                                   // next month

    $days = $this->getJson('/calendar/days?city=chisinau&month=2026-10')->assertOk()->json('days');

    expect($days)->toHaveCount(2)
        ->and($days[0])->toMatchArray(['date' => '2026-10-14', 'count' => 1, 'sports' => ['fotbal']])
        ->and($days[1])->toMatchArray(['date' => '2026-10-16', 'count' => 3, 'sports' => ['tenis', 'fotbal']]);
});

it('counts full matches but not past months', function () {
    $full = calRoom('2026-10-18 18:00', ['max_players' => 2]);
    app(RoomMembership::class)->join($full, User::factory()->create());
    expect($full->fresh()->status)->toBe(RoomStatus::Full);

    expect($this->getJson('/calendar/days?city=chisinau&month=2026-10')->json('days.0.count'))->toBe(1);
    expect($this->getJson('/calendar/days?city=chisinau&month=2026-09')->assertOk()->json('days'))->toBe([]);
});

it('returns an empty list for a month without matches', function () {
    City::firstOrCreate(['slug' => 'chisinau'], ['name' => 'Chișinău']);

    $this->getJson('/calendar/days?city=chisinau&month=2026-12')->assertOk()->assertExactJson(['month' => '2026-12', 'days' => []]);
});

it('validates month and city', function () {
    City::firstOrCreate(['slug' => 'chisinau'], ['name' => 'Chișinău']);

    $this->getJson('/calendar/days?city=chisinau&month=2026-13')->assertUnprocessable()->assertJsonValidationErrors('month');
    $this->getJson('/calendar/days?city=chisinau&month=octombrie')->assertUnprocessable()->assertJsonValidationErrors('month');
    $this->getJson('/calendar/days?city=atlantida&month=2026-10')->assertUnprocessable()->assertJsonValidationErrors('city');
    $this->getJson('/calendar/days?city=chisinau')->assertUnprocessable()
        ->assertJsonValidationErrors(['month' => 'Completează luna.']);
});

it('marks my days and filters to mine when asked', function () {
    $me = User::factory()->create();
    calRoom('2026-10-16 18:00', creator: $me);
    calRoom('2026-10-17 18:00');

    $all = $this->actingAs($me)->getJson('/calendar/days?city=chisinau&month=2026-10')->json('days');
    expect(collect($all)->pluck('mine', 'date')->all())->toBe(['2026-10-16' => true, '2026-10-17' => false]);

    $mine = $this->actingAs($me)->getJson('/calendar/days?city=chisinau&month=2026-10&mine=1')->json('days');
    expect(collect($mine)->pluck('date')->all())->toBe(['2026-10-16']);
});

// ---------- GET /calendar/day ----------

it('lists the matches of one day ordered by time, with public fields only for guests', function () {
    calRoom('2026-10-16 20:00', ['title' => 'Seara', 'venue_type' => 'indoor', 'price' => 50, 'price_collector' => 'venue']);
    calRoom('2026-10-16 18:30', ['title' => 'După-amiaza'], sport: 'tenis');
    calRoom('2026-10-16 19:00', city: 'balti');
    calRoom('2026-10-17 18:00');

    $rooms = $this->getJson('/calendar/day?city=chisinau&date=2026-10-16')->assertOk()->json('rooms');

    expect(array_column($rooms, 'title'))->toBe(['După-amiaza', 'Seara'])
        ->and($rooms[1])->toMatchArray([
            'time' => '20:00',
            'sport' => ['slug' => 'fotbal', 'name' => 'Fotbal', 'var' => '--sport-fotbal'],
            'location' => 'Botanica, Chișinău',
            'venue' => 'Interior',
            'max_players' => 10,
            'current_players_count' => 1,
            'status' => 'open',
            'price' => '50 lei',
        ])
        ->and($rooms[1])->not->toHaveKeys(['players', 'my_role', 'actions']);
});

it('adds role, equipment, players and actions for a logged-in user', function () {
    $me = User::factory()->create();
    $room = calRoom('2026-10-16 20:00', ['equipment_by' => 'players', 'price' => 30, 'price_collector' => 'organizer']);
    $mine = calRoom('2026-10-16 21:00', creator: $me);

    $rooms = collect($this->actingAs($me)->getJson('/calendar/day?city=chisinau&date=2026-10-16')->json('rooms'))->keyBy('id');

    expect($rooms[$room->id])->toMatchArray([
        'my_role' => null,
        'equipment' => 'Fiecare își aduce echipamentul',
        'price_detail' => 'Plătești organizatorului',
        'players_more' => 0,
    ])
        ->and($rooms[$room->id]['actions']['join'])->toBe(route('rooms.join', $room))
        ->and($rooms[$room->id]['actions']['interest'])->toBe(route('rooms.interest', $room))
        ->and($rooms[$room->id]['actions']['leave'])->toBeNull()
        ->and($rooms[$room->id]['players'][0])->toHaveKeys(['name', 'initials', 'avatar'])
        ->and($rooms[$mine->id]['my_role'])->toBe('organizer')
        ->and($rooms[$mine->id]['actions'])->toMatchArray(['join' => null, 'interest' => null, 'leave' => null]);
});

it('rejects an invalid date and hides past hours of today', function () {
    calRoom('2026-10-14 09:00', ['title' => 'Dimineața']);   // before "now"
    calRoom('2026-10-14 19:00', ['title' => 'Diseară']);

    expect(array_column($this->getJson('/calendar/day?city=chisinau&date=2026-10-14')->json('rooms'), 'title'))->toBe(['Diseară']);

    $this->getJson('/calendar/day?city=chisinau&date=2026-02-30')->assertUnprocessable()->assertJsonValidationErrors('date');
    $this->getJson('/calendar/day?city=chisinau&date=16.10.2026')->assertUnprocessable()->assertJsonValidationErrors('date');
});
