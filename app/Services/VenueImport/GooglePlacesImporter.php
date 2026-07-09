<?php

namespace App\Services\VenueImport;

use RuntimeException;

/**
 * Google Places importer — intentionally a disabled stub.
 *
 * Google Places requires a paid API key AND its Terms of Service forbid
 * caching/storing most Place data in a persisted directory like ours. It is
 * therefore not enabled; use the OpenStreetMap (Overpass) importer instead.
 * If you obtain a key and accept Google's terms, implement import() here.
 */
class GooglePlacesImporter implements VenueImporter
{
    public function import(string $city): int
    {
        if (! config('services.google_places.key') && ! env('GOOGLE_PLACES_API_KEY')) {
            throw new RuntimeException(
                'Google Places import is disabled: set GOOGLE_PLACES_API_KEY and review Google\'s '
                .'Terms of Service on storing Place data before enabling. Use --source=osm instead.'
            );
        }

        throw new RuntimeException('Google Places import is not implemented (stub). Use --source=osm.');
    }
}
