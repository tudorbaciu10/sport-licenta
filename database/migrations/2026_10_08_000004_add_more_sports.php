<?php

use App\Models\Sport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Data migration: sports played in Moldova and Romania, added next to the first eight. */
return new class extends Migration
{
    private const SPORTS = [
        'futsal' => 'Futsal',
        'badminton' => 'Badminton',
        'squash' => 'Squash',
        'rugby' => 'Rugby',
        'hochei' => 'Hochei',
        'volei-pe-plaja' => 'Volei pe plajă',
        'ciclism' => 'Ciclism',
        'inot' => 'Înot',
        'escalada' => 'Escaladă',
        'box' => 'Box',
        'sah' => 'Șah',
        'oina' => 'Oină',
    ];

    public function up(): void
    {
        foreach (self::SPORTS as $slug => $name) {
            DB::table('sports')->updateOrInsert(
                ['slug' => $slug],
                ['name' => $name, 'color' => Sport::COLORS[$slug], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        // Only removes sports that have no matches, so no data is lost.
        DB::table('sports')->whereIn('slug', array_keys(self::SPORTS))
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('rooms')->whereColumn('rooms.sport_id', 'sports.id'))
            ->delete();
    }
};
