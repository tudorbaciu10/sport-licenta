<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'user_id', 'venue_category_id', 'name', 'slug', 'description', 'address', 'city',
    'country', 'locality', 'latitude', 'longitude', 'capacity', 'surface', 'is_indoor',
    'price_per_hour', 'currency', 'contact_phone', 'contact_email', 'photo_path',
    'source', 'external_id', 'is_published',
])]
class Venue extends Model
{
    /** @use HasFactory<\Database\Factories\VenueFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'capacity' => 'integer',
            'is_indoor' => 'boolean',
            'is_published' => 'boolean',
            'price_per_hour' => 'decimal:2',
        ];
    }

    /* ---------- Relationships ---------- */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VenueCategory::class, 'venue_category_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /* ---------- Scopes ---------- */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeForCategory(Builder $query, ?int $categoryId): Builder
    {
        return $query->when($categoryId, fn (Builder $q) => $q->where('venue_category_id', $categoryId));
    }

    public function scopeInCity(Builder $query, ?string $city): Builder
    {
        return $query->when($city, fn (Builder $q) => $q->where(function (Builder $inner) use ($city) {
            $inner->where('city', 'like', "%{$city}%")
                ->orWhere('locality', 'like', "%{$city}%");
        }));
    }

    public function scopeInCountry(Builder $query, ?string $country): Builder
    {
        return $query->when($country, fn (Builder $q) => $q->where('country', 'like', "%{$country}%"));
    }

    public function scopeWithSurface(Builder $query, ?string $surface): Builder
    {
        return $query->when($surface, fn (Builder $q) => $q->where('surface', 'like', "%{$surface}%"));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(function (Builder $inner) use ($term) {
            $inner->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        }));
    }

    /* ---------- Helpers ---------- */

    /**
     * The owner-uploaded photo if present, otherwise the category cover image.
     */
    public function photoUrl(): string
    {
        if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
            return Storage::disk('public')->url($this->photo_path);
        }

        return $this->category?->imageUrl()
            ?? asset('assets/images/venue-categories/default.svg');
    }
}
