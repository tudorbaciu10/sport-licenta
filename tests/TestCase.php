<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Tests use RefreshDatabase, which wipes the database. If the config is cached
     * (bootstrap/cache/config.php), phpunit.xml's in-memory SQLite is ignored and the
     * real MySQL database would be wiped. Stop before any test touches it.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        if ($app->configurationIsCached() || config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new RuntimeException(
                'Testele s-au oprit ca să nu șteargă baza de date reală. Rulează „php artisan config:clear” (sau „composer test”) și încearcă din nou.'
            );
        }

        return $app;
    }
}
