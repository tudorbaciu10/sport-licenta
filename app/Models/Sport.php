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
        'futsal' => '#00C7BE',
        'badminton' => '#32ADE6',
        'squash' => '#63E6E2',
        'rugby' => '#AC8E68',
        'hochei' => '#0A84FF',
        'volei-pe-plaja' => '#FFD60A',
        'ciclism' => '#30D158',
        'inot' => '#40C8E0',
        'escalada' => '#FF9F0A',
        'box' => '#FF375F',
        'sah' => '#BF5AF2',
        'oina' => '#64D2FF',
    ];

    /** Name in the current language (lang/{ro,ru}/sports.php), falling back to the stored name. */
    public function label(): string
    {
        $key = 'sports.'.$this->slug;

        return __($key) === $key ? $this->name : __($key);
    }

    /** Blade icon component for <x-dynamic-component>, e.g. "icon.sport-fotbal". */
    public function icon(): string
    {
        return view()->exists('components.icon.sport-'.$this->slug) ? 'icon.sport-'.$this->slug : 'icon.circle-dot';
    }

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
