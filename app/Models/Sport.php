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
}
