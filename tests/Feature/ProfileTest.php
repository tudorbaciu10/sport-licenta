<?php

use App\Models\City;
use App\Models\Room;
use App\Models\Sport;
use App\Models\User;
use App\Services\RoomMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function profileSport(string $slug = 'fotbal', string $name = 'Fotbal'): Sport
{
    return Sport::firstOrCreate(['slug' => $slug], ['name' => $name, 'color' => '#34C759']);
}

function profileRoom(User $creator, array $attrs = []): Room
{
    return app(RoomMembership::class)->create($creator, array_merge([
        'sport_id' => profileSport()->id,
        'city_id' => City::firstOrCreate(['slug' => 'chisinau'], ['name' => 'Chișinău'])->id,
        'title' => 'Meci',
        'location_name' => 'Botanica',
        'match_date_time' => now()->addDay(),
        'max_players' => 10,
    ], $attrs));
}

it('sends new players to complete their profile after registering', function () {
    $this->post('/register', [
        'name' => 'Ana Lungu', 'email' => 'ana@exemplu.md',
        'password' => 'parola-buna', 'password_confirmation' => 'parola-buna',
    ])->assertRedirect(route('profile.edit'))->assertSessionHas('status');
});

it('asks guests to log in for profile and my matches', function () {
    $this->get('/profile')->assertRedirect(route('login'));
    $this->get('/my-matches')->assertRedirect(route('login'));
});

it('saves name, city and levels per sport, dropping sports not played', function () {
    $user = User::factory()->create();
    $fotbal = profileSport();
    $tenis = profileSport('tenis', 'Tenis');
    $city = City::firstOrCreate(['slug' => 'balti'], ['name' => 'Bălți']);
    $user->sports()->attach($tenis->id, ['level' => 'advanced']);

    $this->actingAs($user)->put('/profile', [
        'name' => 'Ion Nou',
        'city_id' => $city->id,
        'sports' => [
            $fotbal->id => ['level' => 'intermediate', 'position' => ' portar '],
            $tenis->id => ['level' => '', 'position' => ''],
        ],
    ])->assertRedirect(route('profile'));

    $user->refresh()->load('sports');
    expect($user->name)->toBe('Ion Nou')
        ->and($user->city_id)->toBe($city->id)
        ->and($user->sports->pluck('slug')->all())->toBe(['fotbal'])
        ->and($user->sports->first()->pivot->level)->toBe('intermediate')
        ->and($user->sports->first()->pivot->position)->toBe('portar');

    $this->actingAs($user)->get('/profile')
        ->assertSee('Ion Nou')->assertSee('Bălți')->assertSee('Mediu · Joc constant · portar');
});

it('rejects an invalid level', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->put('/profile', ['name' => 'X', 'sports' => [profileSport()->id => ['level' => 'pro']]])
        ->assertSessionHasErrors('sports.'.profileSport()->id.'.level');
});

it('uploads, replaces and removes the profile photo', function () {
    Storage::fake('avatars');
    $user = User::factory()->create();

    $this->actingAs($user)->put('/profile', ['name' => $user->name, 'avatar' => UploadedFile::fake()->image('a.jpg', 300, 300)]);
    $first = $user->refresh()->avatar_path;
    Storage::disk('avatars')->assertExists($first);
    $this->get('/profile')->assertSee('uploads/avatars/'.$first, false);

    $this->actingAs($user)->put('/profile', ['name' => $user->name, 'avatar' => UploadedFile::fake()->image('b.png')]);
    Storage::disk('avatars')->assertMissing($first);

    $this->actingAs($user)->put('/profile', ['name' => $user->name, 'remove_avatar' => 1]);
    expect($user->refresh()->avatar_path)->toBeNull();

    $this->actingAs($user)->put('/profile', ['name' => $user->name, 'avatar' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')])
        ->assertSessionHasErrors('avatar');
});

it('lists my upcoming and past matches with my role', function () {
    $me = User::factory()->create();
    $mine = profileRoom($me, ['title' => 'Organizat de mine']);
    $other = profileRoom(User::factory()->create(), ['title' => 'Mă înscriu']);
    app(RoomMembership::class)->join($other, $me);
    $past = profileRoom(User::factory()->create(), ['title' => 'Jucat deja']);
    app(RoomMembership::class)->join($past, $me);
    $past->forceFill(['match_date_time' => now()->subDays(3)])->save();
    profileRoom(User::factory()->create(), ['title' => 'Nu e al meu']);

    $this->actingAs($me)->get('/dashboard')->assertRedirect('/my-matches');

    $upcoming = $this->actingAs($me)->get('/my-matches')->assertOk();
    expect($upcoming->viewData('rooms')->pluck('title')->all())->toBe(['Organizat de mine', 'Mă înscriu']);
    $upcoming->assertSee('Organizezi')->assertSee('Joci')->assertDontSee('Nu e al meu');

    expect($this->actingAs($me)->get('/my-matches?tab=past')->viewData('rooms')->pluck('title')->all())->toBe(['Jucat deja']);

    $this->actingAs($me)->get('/profile')->assertSee('Meciuri viitoare');
});

it('renders auth, profile and my matches in Russian', function () {
    $this->withSession(['locale' => 'ru'])->get('/login')->assertOk()->assertSee('Вход')->assertDontSee('auth.');
    $this->withSession(['locale' => 'ru'])->get('/register')->assertOk()->assertSee('Регистрация');

    $user = User::factory()->create();
    profileSport();
    $this->actingAs($user)->withSession(['locale' => 'ru'])->get('/profile')->assertOk()->assertSee('Мои матчи')->assertDontSee('profile.');
    $this->actingAs($user)->withSession(['locale' => 'ru'])->get('/profile/edit')->assertOk()->assertSee('Не играю');
    $this->actingAs($user)->withSession(['locale' => 'ru'])->get('/my-matches')->assertOk()->assertSee('Прошедшие');
});
