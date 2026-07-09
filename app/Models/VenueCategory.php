<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'icon'])]
class VenueCategory extends Model
{
    /** @use HasFactory<\Database\Factories\VenueCategoryFactory> */
    use HasFactory;

    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class);
    }

    /**
     * Resolve the cover image for this category.
     *
     * Looks in public/assets/images/venue-categories/{slug}/ for cover.(jpg|jpeg|png|webp|svg);
     * falls back to the shared default. Mirrors Sport::imageUrl().
     */
    public function imageUrl(): string
    {
        $dir = "assets/images/venue-categories/{$this->slug}";

        foreach (['cover.jpg', 'cover.jpeg', 'cover.png', 'cover.webp', 'cover.svg'] as $file) {
            if (is_file(public_path("{$dir}/{$file}"))) {
                return asset("{$dir}/{$file}");
            }
        }

        return asset('assets/images/venue-categories/default.svg');
    }
}
