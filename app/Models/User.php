<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'city_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Skill levels, described in words (docs/DESIGN.md §1.2); labels in lang/{ro,ru}/profile.php. */
    public const LEVELS = ['beginner', 'intermediate', 'advanced'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** Sports the user plays, with level and preferred position. */
    public function sports(): BelongsToMany
    {
        return $this->belongsToMany(Sport::class)
            ->withPivot('level', 'position')
            ->withTimestamps();
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? asset('uploads/avatars/'.$this->avatar_path) : null;
    }

    public function createdRooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class)
            ->withPivot('status')
            ->withTimestamps();
    }
}
