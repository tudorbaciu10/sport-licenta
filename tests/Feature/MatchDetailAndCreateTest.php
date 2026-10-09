<?php

use App\Models\City;
use App\Models\Room;
use App\Models\Sport;
use App\Models\User;
use App\Services\RoomMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function detailRoom(array $attrs = [], ?User $creator = null): Room
{
    return app(RoomMembership::class)->create($creator ?? User::factory()->create(), array_merge([
        'sport_id' => Sport::firstOrCreate(['slug' => 'fotbal'], ['name' => 'Fotbal', 'color' => '#34C759'])->id,
        'city_id' => City::firstOrCreate(['slug' => 'chisinau'], ['name' => 'Chișinău'])->id,
        'title' => 'Minifotbal joi',
        'location_name' => 'Botanica',
        'match_date_time' => now()->addDays(2)->setTime(19, 0),
        'max_players' => 10,
    ], $attrs));
}

function validPayload(array $extra = []): array
{
    return array_merge([
        'sport_id' => Sport::firstOrCreate(['slug' => 'fotbal'], ['name' => 'Fotbal', 'color' => '#34C759'])->id,
        'city_id' => City::firstOrCreate(['slug' => 'chisinau'], ['name' => 'Chișinău'])->id,
        'title' => 'Fotbal joi',
        'location_name' => 'Botanica',
        'match_date_time' => now()->addDays(2)->format('Y-m-d\TH:i'),
        'max_players' => 10,
    ], $extra);
}

it('stores price, collector and equipment', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('rooms.store'), validPayload(['price' => 50, 'price_collector' => 'venue', 'equipment_by' => 'organizer']))
        ->assertRedirect();

    expect(Room::first())
        ->price->toBe(50)
        ->price_collector->toBe('venue')
        ->equipment_by->toBe('organizer');
});

it('requires a collector for paid matches and drops it for free ones', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('rooms.store'), validPayload(['price' => 40]))
        ->assertSessionHasErrors('price_collector');

    $this->actingAs($user)->post(route('rooms.store'), validPayload(['price' => 0, 'price_collector' => 'venue']))
        ->assertSessionHasNoErrors();
    expect(Room::first()->price_collector)->toBeNull();
});

it('shows Romanian validation messages', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('rooms.store'), validPayload(['title' => '']))
        ->assertSessionHasErrors(['title' => 'Scrie un titlu pentru meci.']);
});

it('filters the coming weekend and free matches', function () {
    Carbon::setTestNow(Carbon::parse('next wednesday 10:00'));
    $saturday = detailRoom(['title' => 'Sâmbătă', 'match_date_time' => Carbon::parse('saturday this week 18:00'), 'price' => 0]);
    detailRoom(['title' => 'Vineri', 'match_date_time' => Carbon::parse('friday this week 18:00'), 'price' => 50, 'price_collector' => 'venue']);
    detailRoom(['title' => 'Luni', 'match_date_time' => Carbon::parse('monday next week 18:00'), 'price' => 0]);

    $titles = fn (array $q) => $this->get(route('rooms.index', $q))->viewData('rooms')->pluck('title')->sort()->values()->all();

    expect($titles(['when' => 'weekend']))->toBe(['Sâmbătă'])
        ->and($titles(['price' => 'free']))->toBe(['Luni', 'Sâmbătă']);

    $this->get('/rooms?when=yesterday')->assertSessionHasErrors('when');
    Carbon::setTestNow();
});

it('sends a guest to login and back to the same match', function () {
    $room = detailRoom();
    $user = User::factory()->create(['password' => 'secret-pass']);

    $this->get(route('rooms.show', $room))
        ->assertOk()
        ->assertSee('Intră în cont ca să ocupi un loc')
        ->assertSee(route('login'), false);

    $this->post('/login', ['email' => $user->email, 'password' => 'secret-pass'])
        ->assertRedirect(route('rooms.show', $room));
});

it('shows the right action for each viewer', function () {
    $creator = User::factory()->create();
    $room = detailRoom(['price' => 30, 'price_collector' => 'organizer', 'equipment_by' => 'players'], $creator);
    $player = User::factory()->create();
    app(RoomMembership::class)->join($room, $player);

    $this->actingAs($creator)->get(route('rooms.show', $room))
        ->assertSee('Tu organizezi acest meci')->assertSee('Distribuie')->assertDontSee('Ies din meci')
        ->assertSee('30 lei de persoană')->assertSee('Plătești organizatorului')->assertSee('Fiecare își aduce echipamentul');

    $this->actingAs($player)->get(route('rooms.show', $room))
        ->assertSee('Ești în echipă')->assertSee('Ies din meci');

    $this->actingAs(User::factory()->create())->get(route('rooms.show', $room))
        ->assertSee('Ocupă un loc')->assertSee('Mă interesează');
});

it('disables joining a full match', function () {
    $room = detailRoom(['max_players' => 2]);
    app(RoomMembership::class)->join($room, User::factory()->create());

    $this->actingAs(User::factory()->create())->get(route('rooms.show', $room))
        ->assertSee('Meci complet')
        ->assertSee('disabled', false);
});

it('renders the create form in steps and in Russian', function () {
    $this->actingAs(User::factory()->create());
    Sport::firstOrCreate(['slug' => 'fotbal'], ['name' => 'Fotbal', 'color' => '#34C759']);

    $this->get(route('rooms.create'))->assertOk()
        ->assertSee('Pasul 4 din 4')->assertSee('Publică meciul')->assertSee('data-step="4"', false);

    $this->withSession(['locale' => 'ru'])->get(route('rooms.create'))->assertOk()
        ->assertSee('Создать матч')->assertSee('Футбол')->assertDontSee('match.create.');
});

it('saves the map pin and shows it on the match page', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('rooms.store'), validPayload(['latitude' => '47.0105', 'longitude' => '28.8638']))
        ->assertRedirect();

    $room = Room::latest('id')->first();
    expect($room->latitude)->toBe(47.0105)->and($room->longitude)->toBe(28.8638);

    $this->get(route('rooms.show', $room))
        ->assertSee('roomMap(47.0105, 28.8638)', false)
        ->assertSee('https://www.google.com/maps/search/?api=1&amp;query=47.0105,28.8638', false);
});

it('works without a pin and rejects half a pin or a match too far ahead', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('rooms.store'), validPayload())->assertRedirect();
    expect(Room::latest('id')->first()->hasPin())->toBeFalse();

    $this->post(route('rooms.store'), validPayload(['latitude' => '47.01']))
        ->assertSessionHasErrors(['longitude' => 'Punctul de pe hartă nu e valid.']);
    $this->post(route('rooms.store'), validPayload(['match_date_time' => now()->addMonths(7)->format('Y-m-d\TH:i')]))
        ->assertSessionHasErrors(['match_date_time' => 'Poți programa un meci cel mult 6 luni înainte.']);
});

it('validates in the visitor\'s language, Russian included', function () {
    $this->actingAs(User::factory()->create())->withSession(['locale' => 'ru'])
        ->post(route('rooms.store'), validPayload(['sport_id' => '', 'match_date_time' => '', 'location_name' => '']))
        ->assertSessionHasErrors([
            'sport_id' => 'Выберите вид спорта.',
            'match_date_time' => 'Выберите день и время матча.',
            'location_name' => 'Напишите, где играете (площадка, адрес).',
        ]);
});

it('renders the planner: calendar, map and translated browser messages', function () {
    $this->actingAs(User::factory()->create())->get(route('rooms.create'))
        ->assertOk()
        ->assertSee('createMatch(', false)
        ->assertSee('class="mcal"', false)
        ->assertSee('Alege pe hartă')
        ->assertSee('Alege ziua și ora meciului.')       // client-side message from lang/ro/validation.php
        ->assertDontSee('type="datetime-local"', false);

    $this->withSession(['locale' => 'ru'])->get(route('rooms.create'))
        ->assertSee('Выбрать на карте')
        ->assertSee('Выберите день и время матча.')
        ->assertDontSee('>match.create.', false);
});
