<?php

use App\Models\Sport;
use App\Models\User;

test('guests are redirected from the admin panel', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('non-admin users are forbidden from the admin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('an admin can view the admin panel', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

test('an admin can add a sport', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.sports.store'), ['name' => 'Squash'])
        ->assertRedirect();

    $this->assertDatabaseHas('sports', ['name' => 'Squash', 'slug' => 'squash']);
});

test('a non-admin cannot add a sport', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.sports.store'), ['name' => 'Squash'])
        ->assertForbidden();

    $this->assertDatabaseMissing('sports', ['name' => 'Squash']);
});

test('an admin can delete a sport', function () {
    $admin = User::factory()->admin()->create();
    $sport = Sport::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.sports.destroy', $sport))
        ->assertRedirect();

    $this->assertDatabaseMissing('sports', ['id' => $sport->id]);
});

test('the isAdmin helper reflects the role', function () {
    expect(User::factory()->admin()->create()->isAdmin())->toBeTrue();
    expect(User::factory()->create()->isAdmin())->toBeFalse();
});
