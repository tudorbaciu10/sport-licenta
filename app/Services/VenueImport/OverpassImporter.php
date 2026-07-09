<?php

namespace App\Services\VenueImport;

use App\Models\Venue;
use App\Models\VenueCategory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Imports facilities from OpenStreetMap via the Overpass API.
 *
 * OSM data is under the ODbL and is free to store, unlike Google Places.
 * We only pull public leisure facilities (pitches, sports/fitness centres);
 * owners can later claim a listing to add pricing/contact details.
 */
class OverpassImporter implements VenueImporter
{
    private const ENDPOINT = 'https://overpass-api.de/api/interpreter';

    public function import(string $city): int
    {
        $query = $this->buildQuery($city);

        $response = Http::timeout(60)
            ->asForm()
            ->post(self::ENDPOINT, ['data' => $query]);

        if (! $response->successful()) {
            return 0;
        }

        $elements = $response->json('elements', []);
        $categories = VenueCategory::pluck('id', 'slug');
        $count = 0;

        foreach ($elements as $el) {
            $tags = $el['tags'] ?? [];
            $name = $tags['name'] ?? null;

            if (! $name) {
                continue; // skip unnamed features — not useful in a directory
            }

            $lat = $el['lat'] ?? ($el['center']['lat'] ?? null);
            $lon = $el['lon'] ?? ($el['center']['lon'] ?? null);
            $externalId = ($el['type'] ?? 'node').'/'.($el['id'] ?? '');
            $slug = $this->categorySlug($tags);

            Venue::updateOrCreate(
                ['source' => 'osm', 'external_id' => $externalId],
                [
                    'name' => $name,
                    'slug' => Str::slug($name).'-osm-'.($el['id'] ?? Str::lower(Str::random(5))),
                    'venue_category_id' => $categories[$slug] ?? null,
                    'city' => $city,
                    'country' => 'Moldova',
                    'address' => $tags['addr:street'] ?? null,
                    'surface' => $tags['surface'] ?? null,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'is_published' => true,
                ],
            );

            $count++;
        }

        return $count;
    }

    private function buildQuery(string $city): string
    {
        $city = str_replace('"', '', $city);

        return <<<OVERPASS
        [out:json][timeout:60];
        area["name"="{$city}"]->.searchArea;
        (
          node["leisure"="pitch"](area.searchArea);
          way["leisure"="pitch"](area.searchArea);
          node["leisure"="sports_centre"](area.searchArea);
          way["leisure"="sports_centre"](area.searchArea);
          node["leisure"="fitness_centre"](area.searchArea);
          way["leisure"="fitness_centre"](area.searchArea);
        );
        out center tags 80;
        OVERPASS;
    }

    private function categorySlug(array $tags): string
    {
        $leisure = $tags['leisure'] ?? '';
        $sport = $tags['sport'] ?? '';

        if (in_array($leisure, ['sports_centre', 'fitness_centre'], true)) {
            return 'halls-gyms';
        }

        if ($leisure === 'pitch') {
            if (in_array($sport, ['tennis', 'padel', 'badminton', 'table_tennis', 'squash'], true)) {
                return 'racket-courts';
            }

            return 'pitches';
        }

        return 'other';
    }
}
