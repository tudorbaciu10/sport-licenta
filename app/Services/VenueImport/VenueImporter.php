<?php

namespace App\Services\VenueImport;

interface VenueImporter
{
    /**
     * Import rentable facilities for the given city.
     *
     * @return int Number of venues created or updated.
     */
    public function import(string $city): int;
}
