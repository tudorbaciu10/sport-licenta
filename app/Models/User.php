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
            'is_support' => 'boolean',
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

    /** "Ion Popescu" → "IP", same rule as <x-avatar>. */
    public function initials(): string
    {
        return mb_strtoupper(collect(preg_split('/\s+/u', trim((string) $this->name)))
            ->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->join(''));
    }

    /**
     * Links built on the server from the stored handles (never a user-typed URL).
     *
     * @return array<string, string> network => url
     */
    public function socialLinks(): array
    {
        return array_filter([
            'telegram' => $this->telegram ? 'https://t.me/'.ltrim($this->telegram, '@') : null,
            'whatsapp' => $this->whatsapp ? 'https://wa.me/'.preg_replace('/\D/', '', $this->whatsapp) : null,
            'instagram' => $this->instagram ? 'https://instagram.com/'.ltrim($this->instagram, '@') : null,
            'facebook' => $this->facebook ? 'https://facebook.com/'.$this->facebook : null,
        ]);
    }

    /** The user's private thread with the support team. */
    public function supportMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'support_user_id');
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
