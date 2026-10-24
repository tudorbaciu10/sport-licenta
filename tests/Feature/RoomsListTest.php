<?php

use App\Models\City;
use App\Models\Room;
use App\Models\Sport;
use App\Models\User;
use App\Services\RoomMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function listRoom(array $attrs = []): Room
{
    return app(RoomMembership::class)->create(User::factory()->create(), array_merge([
        'sport_id' => Sport::firstOrCreate(['slug' => 'fotbal'], ['name' => 'Fotbal', 'color' => '#34C759'])->id,
        'city_id' => City::firstOrCreate(['slug' => 'chisinau'], ['name' => 'Chișinău'])->id,
        'title' => 'Meci',
        'location_name' => 'Botanica',
        'match_date_time' => now()->addDay()->setTime(19, 0),
        'max_players' => 10,
    ], $attrs));
}

it('groups matches by day with Today / Tomorrow headings', function () {
    listRoom(['title' => 'Diseară', 'match_date_time' => now()->addMinutes(90)->min(now()->endOfDay())]);
    listRoom(['title' => 'Mâine seară', 'match_date_time' => now()->addDay()->setTime(19, 0)]);
    listRoom(['title' => 'Mai târziu', 'match_date_time' => now()->addDays(4)->setTime(19, 0)]);

    $html = $this->get('/rooms')->assertOk()->getContent();

    expect($html)->toContain('>Mâine</h2>')
        ->toContain(ucfirst(now()->addDays(4)->locale('ro')->isoFormat('dddd, D MMMM')).'</h2>')
        ->toContain('3 meciuri');
});

it('marks the active chip and lets it be switched off', function () {
    listRoom();

    $page = $this->get('/rooms?venue=indoor&city=chisinau');

    $page->assertOk()
        ->assertSee('aria-current="true"', false)
        // the active "Interior" chip links back without venue, keeping the city
        ->assertSee('href="'.route('rooms.index', ['city' => 'chisinau']).'"', false);
});

it('shows an empty state with a create action when nothing matches', function () {
    listRoom();

    $this->get('/rooms?q=nimic-de-gasit')
        ->assertOk()
        ->assertSee('Niciun meci aici încă')
        ->assertSee(route('rooms.create'), false)
        ->assertSee('Șterge filtrele');
});

it('is visible to guests and translated to Russian', function () {
    listRoom();

    $this->withSession(['locale' => 'ru'])->get('/rooms')
        ->assertOk()
        ->assertSee('Матчи')
        ->assertSee('Кишинёв')
        ->assertSee('Футбол')
        ->assertDontSee('rooms.index.');
});

it('picks the city from a rounded dropdown that keeps the other filters', function () {
    listRoom();
    City::firstOrCreate(['slug' => 'balti'], ['name' => 'Bălți']);

    $this->get('/rooms?q=Meci&city=chisinau')->assertOk()
        ->assertSee('class="dropdown__button"', false)
        ->assertSee('aria-current="true"', false)
        ->assertSee(e(route('rooms.index', ['city' => 'balti', 'q' => 'Meci'])), false)   // other filters kept
        ->assertSee(e(route('rooms.index', ['q' => 'Meci'])), false)                      // "Toate orașele"
        ->assertDontSee('<select id="city"', false);
});

it('shows the number of active filters on the phone filter button', function () {
    listRoom();

    $this->get('/rooms?venue=indoor&date='.today()->toDateString())->assertOk()
        ->assertSeeInOrder(['rooms-filter-btn', 'rooms-filter-btn__count" aria-hidden="true">2<'], false);
});

it('lists matches as compact rows for phones and keeps the desktop cards', function () {
    listRoom(['title' => 'Meci compact']);

    $html = $this->get('/rooms')->assertOk()->getContent();

    expect($html)->toContain('class="match-row"')->toContain('class="match-card"')
        ->toContain('id="sheet-rooms-filters"');
});

it('keeps the list/map choice in filter links', function () {
    listRoom();

    $this->get('/rooms?view=map')->assertOk()
        ->assertSee(e(route('rooms.index', ['view' => 'map', 'venue' => 'indoor'])), false)
        ->assertSee('id="rooms-map"', false);

    $this->get('/rooms')->assertDontSee('id="rooms-map"', false);
});

it('configures the map with the filtered data url, without the city, only in map view', function () {
    listRoom();

    $html = $this->get('/rooms?view=map&city=chisinau&sport=fotbal')->assertOk()->getContent();

    expect($html)->toContain('roomsMap(')
        ->toContain('data-url="'.e(route('rooms.map', ['sport' => 'fotbal'])).'"')
        ->toContain('rooms-map.js');
    expect($this->get('/rooms')->getContent())->not->toContain('roomsMap(')->not->toContain('rooms-map.js');
});
