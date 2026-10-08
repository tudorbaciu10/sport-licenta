<?php

use App\Models\Sport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data migration: sports.color follows the --sport-* tokens in public/assets/css/tokens.css
 * (docs/DESIGN.md §4.3), so the database and the CSS share one source of truth.
 */
return new class extends Migration
{
    private const OLD = [
        'fotbal' => '#5BE08F', 'baschet' => '#FF8A3D', 'tenis' => '#DCEB4B', 'volei' => '#FFD86B',
        'handbal' => '#9EA8FF', 'alergare' => '#FF7A6B', 'tenis-de-masa' => '#7FE3F0', 'padel' => '#6BF2CF',
    ];

    public function up(): void
    {
        foreach (Sport::COLORS as $slug => $color) {
            DB::table('sports')->where('slug', $slug)->update(['color' => $color]);
        }
    }

    public function down(): void
    {
        foreach (self::OLD as $slug => $color) {
            DB::table('sports')->where('slug', $slug)->update(['color' => $color]);
        }
    }
};
