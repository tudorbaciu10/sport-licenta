<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'icon', 'color'])]
class Sport extends Model
{
    use HasFactory;

    /** Must match the --sport-* tokens in public/assets/css/tokens.css (docs/DESIGN.md §4.3). */
    public const COLORS = [
        'fotbal' => '#34C759',
        'baschet' => '#FF9500',
        'tenis' => '#FFCC00',
        'volei' => '#A2845E',
        'handbal' => '#FF2D55',
        'alergare' => '#007AFF',
        'tenis-de-masa' => '#AF52DE',
        'padel' => '#30B0C7',
    ];

    /** CSS custom property for this sport, e.g. "--sport-fotbal" (table tennis is "--sport-tenis-masa"). */
    public function cssVar(): string
    {
        return '--sport-'.($this->slug === 'tenis-de-masa' ? 'tenis-masa' : $this->slug);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
