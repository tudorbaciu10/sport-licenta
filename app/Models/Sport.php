<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug'])]
class Sport extends Model
{
    /** @use HasFactory<\Database\Factories\SportFactory> */
    use HasFactory;

    /**
     * Users who play this sport (with per-sport skill on the pivot).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_sport')
            ->withPivot('skill_level')
            ->withTimestamps();
    }

    /**
     * Events for this sport.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Resolve the cover image for this sport.
     *
     * Looks inside public/assets/images/sports/{slug}/ for a file named
     * cover.(jpg|jpeg|png|webp|svg) so a user can drop their own photo in the
     * sport's folder; falls back to the shared default cover otherwise.
     */
    public function imageUrl(): string
    {
        $dir = "assets/images/sports/{$this->slug}";

        foreach (['cover.jpg', 'cover.jpeg', 'cover.png', 'cover.webp', 'cover.svg'] as $file) {
            if (is_file(public_path("{$dir}/{$file}"))) {
                return asset("{$dir}/{$file}");
            }
        }

        return asset('assets/images/sports/default.svg');
    }
}

