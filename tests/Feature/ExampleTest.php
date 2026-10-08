<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

// The landing page's search component reads cities/sports/rooms, so it needs a migrated DB.
uses(RefreshDatabase::class);

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
