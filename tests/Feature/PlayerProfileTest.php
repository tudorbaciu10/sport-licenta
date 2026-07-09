<?php

use App\Models\Sport;
use App\Models\User;

test('the player profile page renders', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.details.edit'))
        ->assertOk();
});

test('a user can update their player profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.details.update'), [
            'bio' => 'I play twice a week.',
            'city' => 'Chișinău',
            'skill_level' => 4,
        ])
        ->assertRedirect(route('profile.details.edit'));

    $user->refresh();
    expect($user->profile)->not->toBeNull();
    expect($user->profile->bio)->toBe('I play twice a week.');
    expect($user->profile->city)->toBe('Chișinău');
    expect($user->profile->skill_level)->toBe(4);
});

test('a user can sync sports with a per-sport skill level', function () {
    $user = User::factory()->create();
    $football = Sport::factory()->create();
    $tennis = Sport::factory()->create();

    $this->actingAs($user)->patch(route('profile.details.update'), [
        'city' => 'Chișinău',
        'skill_level' => 3,
        'sports' => [$football->id, $tennis->id],
        'sport_skill' => [
            $football->id => 5,
            $tennis->id => 2,
        ],
    ]);

    $user->refresh();
    expect($user->sports)->toHaveCount(2);
    expect($user->sports->firstWhere('id', $football->id)->pivot->skill_level)->toBe(5);
    expect($user->sports->firstWhere('id', $tennis->id)->pivot->skill_level)->toBe(2);
});
