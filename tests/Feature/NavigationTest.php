<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('hides "Meciurile mele" and "Profil" from guests and offers login instead', function () {
    $html = $this->get('/rooms')->assertOk()->getContent();

    expect($html)
        ->not->toContain('href="'.route('my-matches').'"')
        ->not->toContain('href="'.route('profile').'"')
        ->not->toContain('Meciurile mele')
        ->toContain('href="'.route('login').'"')
        ->toContain('href="'.route('register').'"')
        ->toContain('>Intră</span>');   // bottom tab bar
});

it('shows the full navigation to logged-in users', function () {
    $html = $this->actingAs(User::factory()->create())->get('/rooms')->assertOk()->getContent();

    expect($html)
        ->toContain('href="'.route('my-matches').'"')
        ->toContain('href="'.route('profile').'"')
        ->toContain('Meciurile mele')
        ->not->toContain('>Intră</span>');
});

/** A match by $organizer with $player joined and one message from the player, unread by the organizer. */
function navUnreadRoom(User $organizer, User $player): void
{
    $membership = app(\App\Services\RoomMembership::class);
    $room = $membership->create($organizer, [
        'sport_id' => \App\Models\Sport::firstOrCreate(['slug' => 'fotbal'], ['name' => 'Fotbal', 'color' => '#34C759'])->id,
        'city_id' => \App\Models\City::firstOrCreate(['slug' => 'chisinau'], ['name' => 'Chișinău'])->id,
        'title' => 'Meci',
        'location_name' => 'Botanica',
        'match_date_time' => now()->addDay()->setTime(19, 0),
        'max_players' => 10,
    ]);
    $membership->join($room, $player);
    \App\Models\Message::forceCreate(['room_id' => $room->id, 'user_id' => $player->id, 'body' => 'Salut']);
}

it('keeps the header markup (hidden on phones by CSS) and marks unread chat on the "Ale mele" tab', function () {
    $organizer = User::factory()->create();
    navUnreadRoom($organizer, User::factory()->create());

    $html = $this->actingAs($organizer)->get('/')->assertOk()->getContent();

    expect($html)->toContain('class="header-chat"')
        ->toContain('bottom-nav__dot');
});

it('has no unread dot when everything is read', function () {
    $this->actingAs(User::factory()->create())->get('/')->assertOk()->assertDontSee('bottom-nav__dot', false);
});
