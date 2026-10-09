<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug'])]
class City extends Model
{
    use HasFactory;

    /** City centres [lat, lng]: where the map opens on the create form. */
    public const CENTERS = [
        'chisinau' => [47.0105, 28.8638],
        'balti' => [47.7617, 27.9289],
        'cahul' => [45.9075, 28.1944],
        'orhei' => [47.3831, 28.8231],
        'ungheni' => [47.2108, 27.8006],
        'comrat' => [46.2956, 28.6564],
        'soroca' => [48.1558, 28.2975],
    ];

    /** @return array{0: float, 1: float} */
    public function center(): array
    {
        return self::CENTERS[$this->slug] ?? self::CENTERS['chisinau'];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /** Name in the current language (lang/{ro,ru}/cities.php), falling back to the stored name. */
    public function label(): string
    {
        $key = 'cities.'.$this->slug;

        return __($key) === $key ? $this->name : __($key);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
