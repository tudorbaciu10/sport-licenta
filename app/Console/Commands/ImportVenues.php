<?php

namespace App\Console\Commands;

use App\Services\VenueImport\GooglePlacesImporter;
use App\Services\VenueImport\OverpassImporter;
use App\Services\VenueImport\VenueImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportVenues extends Command
{
    protected $signature = 'venues:import {city : City to import facilities for} {--source=osm : osm|google}';

    protected $description = 'Import rentable facilities for a city from an external source (OpenStreetMap by default).';

    public function handle(): int
    {
        $city = $this->argument('city');
        $source = $this->option('source');

        $importer = $this->resolve($source);

        if (! $importer) {
            $this->error("Unknown source '{$source}'. Use 'osm' or 'google'.");

            return self::FAILURE;
        }

        $this->info("Importing facilities for \"{$city}\" from {$source}...");

        try {
            $count = $importer->import($city);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Done. {$count} facilities imported/updated.");

        return self::SUCCESS;
    }

    private function resolve(string $source): ?VenueImporter
    {
        return match ($source) {
            'osm' => new OverpassImporter(),
            'google' => new GooglePlacesImporter(),
            default => null,
        };
    }
}
