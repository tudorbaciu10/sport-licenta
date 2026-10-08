<?php

namespace App\Models;

use App\Enums\ParticipationStatus;
use App\Enums\RoomStatus;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'sport_id', 'city_id', 'title', 'description', 'location_name', 'venue_type',
    'match_date_time', 'max_players', 'rules',
])]
class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    public const VENUE_TYPES = ['indoor' => 'Acoperit', 'outdoor' => 'În aer liber'];

    protected function casts(): array
    {
        return [
            'match_date_time' => 'datetime',
            'rules' => 'array',
            'status' => RoomStatus::class,
            'max_players' => 'integer',
            'current_players_count' => 'integer',
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

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** Everyone attached to the room, joined or just interested. */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('status')
            ->withTimestamps();
    }

    public function players(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', ParticipationStatus::Joined->value);
    }

    public function interested(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', ParticipationStatus::Interested->value);
    }

    /** Rooms people can still act on: open or full, and not yet started. */
    public function scopeUpcoming(Builder $query): void
    {
        $query->whereIn('status', [RoomStatus::Open, RoomStatus::Full])
            ->where('match_date_time', '>', now());
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['city'] ?? null, fn ($q, $slug) => $q->whereRelation('city', 'slug', $slug))
            ->when($filters['sport'] ?? null, fn ($q, $slug) => $q->whereRelation('sport', 'slug', $slug))
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('match_date_time', $date))
            ->when($filters['time'] ?? null, fn ($q, $time) => $q->whereTime('match_date_time', '>=', $time))
            ->when($filters['venue'] ?? null, fn ($q, $venue) => $q->where('venue_type', $venue))
            ->when($filters['free'] ?? null, fn ($q) => $q->where('status', RoomStatus::Open))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $term).'%';
                $q->where(fn ($w) => $w
                    ->where('title', 'like', $like)
                    ->orWhere('location_name', 'like', $like)
                    ->orWhereRelation('sport', 'name', 'like', $like)
                    ->orWhereRelation('city', 'name', 'like', $like));
            });
    }

    /**
     * Display state for badges: "full" when no spots are left, "almost" when 20% or fewer
     * remain (at least 1), otherwise "open". Read-only; join rules live in RoomMembership.
     */
    public function availability(): string
    {
        $left = $this->spotsLeft();

        return match (true) {
            $left === 0 || $this->status === RoomStatus::Full => 'full',
            $left <= max(1, (int) floor($this->max_players * .2)) => 'almost',
            default => 'open',
        };
    }

    public function venueLabel(): ?string
    {
        return self::VENUE_TYPES[$this->venue_type] ?? null;
    }

    public function spotsLeft(): int
    {
        return max(0, $this->max_players - $this->current_players_count);
    }

    public function isJoinable(): bool
    {
        return $this->status === RoomStatus::Open && $this->match_date_time->isFuture();
    }

    public function participationOf(?User $user): ?ParticipationStatus
    {
        if (! $user) {
            return null;
        }

        $status = $this->participants->firstWhere('id', $user->id)?->pivot->status;

        return $status ? ParticipationStatus::from($status) : null;
    }
}
