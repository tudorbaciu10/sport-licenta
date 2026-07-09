<?php

use App\Models\User;
use App\Models\VenueCategory;

test('a non-admin cannot manage facility categories', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.venue-categories.store'), ['name' => 'Pools'])
        ->assertForbidden();

    $this->assertDatabaseMissing('venue_categories', ['name' => 'Pools']);
});

test('an admin can add a facility category', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.venue-categories.store'), ['name' => 'Swimming pools', 'icon' => '🏊'])
        ->assertRedirect();

    $this->assertDatabaseHas('venue_categories', ['name' => 'Swimming pools', 'slug' => 'swimming-pools']);
});

test('an admin can delete a facility category', function () {
    $admin = User::factory()->admin()->create();
    $category = VenueCategory::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.venue-categories.destroy', $category))
        ->assertRedirect();

    $this->assertDatabaseMissing('venue_categories', ['id' => $category->id]);
});
