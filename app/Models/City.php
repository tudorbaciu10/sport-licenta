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
