<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A chat message, either in a match chat (room_id) or in a user's support thread (support_user_id). */
#[Fillable(['room_id', 'support_user_id', 'user_id', 'body'])]
class Message extends Model
{
    public const MAX_LENGTH = 1000;

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * What the browser needs to draw a bubble. Only public profile data: name, initials, photo.
     *
     * @return array<string, mixed>
     */
    public function toChat(): array
    {
        $author = $this->author;
        $at = $this->created_at->timezone(config('app.timezone'));

        return [
            'id' => $this->id,
            'body' => $this->body,
            'user_id' => $this->user_id,
            'author' => $author->name,
            'initials' => $author->initials(),
            'avatar' => $author->avatarUrl(),
            'support' => (bool) $author->is_support && $this->support_user_id !== null && $this->support_user_id !== $author->id,
            'day' => $at->toDateString(),
            'time' => $at->format('H:i'),
        ];
    }
}
