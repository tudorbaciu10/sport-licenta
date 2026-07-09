<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'user_id', 'sport_id', 'venue_id', 'title', 'description',
    'start_time', 'end_time', 'city', 'max_participants', 'skill_level', 'status',
])]
class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_FULL = 'full';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_COMPLETED = 'completed';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'max_participants' => 'integer',
            'skill_level' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Users who have joined this event (via the event_participants pivot).
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_participants')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Whether the event has reached its participant capacity.
     */
    public function isFull(): bool
    {
        if ($this->max_participants === null) {
            return false;
        }

        return $this->participants()->count() >= $this->max_participants;
    }

    /**
     * Whether the given user has already joined this event.
     */
    public function hasParticipant(User $user): bool
    {
        return $this->participants()->whereKey($user->getKey())->exists();
    }

    /**
     * Scope: only events that have not yet started.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_time', '>=', now());
    }

    /**
     * Scope: filter by sport id (no-op when null).
     */
    public function scopeForSport(Builder $query, ?int $sportId): Builder
    {
        return $query->when($sportId, fn (Builder $q) => $q->where('sport_id', $sportId));
    }

    /**
     * Scope: filter by city, matching either the event or its venue (no-op when blank).
     */
    public function scopeInCity(Builder $query, ?string $city): Builder
    {
        return $query->when($city, fn (Builder $q) => $q->where(function (Builder $inner) use ($city) {
            $inner->where('city', 'like', "%{$city}%")
                ->orWhereHas('venue', fn (Builder $v) => $v->where('city', 'like', "%{$city}%"));
        }));
    }

    /**
     * Scope: only events starting on or after the given date (no-op when blank).
     */
    public function scopeFromDate(Builder $query, ?string $date): Builder
    {
        return $query->when($date, fn (Builder $q) => $q->whereDate('start_time', '>=', $date));
    }
}
