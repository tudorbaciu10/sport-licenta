<?php

use App\Models\City;
use App\Models\Room;
use App\Models\Sport;
use App\Models\User;
use App\Services\RoomMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function homeRoom(string $citySlug, string $sportSlug, array $attrs = []): Room
{
    $names = ['chisinau' => 'Chișinău', 'balti' => 'Bălți', 'fotbal' => 'Fotbal', 'tenis' => 'Tenis'];

    return app(RoomMembership::class)->create(User::factory()->create(), array_merge([
        'sport_id' => Sport::firstOrCreate(['slug' => $sportSlug], ['name' => $names[$sportSlug], 'color' => '#34C759'])->id,
        'city_id' => City::firstOrCreate(['slug' => $citySlug], ['name' => $names[$citySlug]])->id,
        'title' => 'Meci '.$citySlug,
        'location_name' => 'Teren',
        'match_date_time' => now()->addDay(),
        'max_players' => 10,
    ], $attrs));
}

it('shows real open-match counts per sport for Chișinău by default', function () {
    homeRoom('chisinau', 'fotbal');
    homeRoom('chisinau', 'fotbal');
    homeRoom('chisinau', 'tenis');
    homeRoom('balti', 'fotbal');

    $page = $this->get('/')->assertOk();

    expect($page->viewData('city')->slug)->toBe('chisinau');
    $counts = $page->viewData('sports')->pluck('open_count', 'slug')->all();
    expect($counts)->toBe(['fotbal' => 2, 'tenis' => 1]);

    $page->assertSee('Ce joci azi?')
        ->assertSee('2 meciuri')
        ->assertSee('Toate')                 // first tile of the phone rail
        ->assertSee('3 meciuri')             // all open matches in the city
        ->assertSee('Urmează în Chișinău')
        ->assertSee('Meci chisinau')
        ->assertDontSee('Meci balti')
        ->assertSee(e(route('rooms.index', ['city' => 'chisinau', 'sport' => 'fotbal'])), false);
});

it('switches city from the selector and uses the player\'s own city', function () {
    homeRoom('chisinau', 'fotbal');
    homeRoom('balti', 'tenis');

    expect($this->get('/?city=balti')->viewData('sports')->pluck('open_count', 'slug')->all())
        ->toBe(['fotbal' => 0, 'tenis' => 1]);

    $user = User::factory()->create(['city_id' => City::where('slug', 'balti')->value('id')]);
    expect($this->actingAs($user)->get('/')->viewData('city')->slug)->toBe('balti');
});

it('invites guests to sign up and members to organise', function () {
    $this->get('/')->assertOk()
        ->assertSee('Ce joci azi?')   // no cities yet: still renders
        ->assertSee('Creează cont gratuit')
        ->assertSee('Fii primul care organizează');

    $this->actingAs(User::factory()->create())->get('/')
        ->assertDontSee('Creează cont gratuit')
        ->assertSee('Organizează un meci');
});

it('no longer ships the old 3D landing assets', function () {
    $this->get('/')->assertOk()
        ->assertDontSee('three.module')
        ->assertDontSee('gsap')
        ->assertDontSee('Big Shoulders');
});

it('renders the home page in Russian', function () {
    homeRoom('chisinau', 'fotbal');

    $this->withSession(['locale' => 'ru'])->get('/')->assertOk()
        ->assertSee('Во что играем сегодня?')
        ->assertSee('Скоро · Кишинёв')
        ->assertSee('Футбол')
        ->assertDontSee('home.');
});
