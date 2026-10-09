<?php

namespace App\Support;

use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/** One chat thread: a match chat or a user's support thread. Shared by the controller and the views. */
final class Conversation
{
    private function __construct(public readonly ?Room $room, public readonly ?User $owner) {}

    public static function room(Room $room): self
    {
        return new self($room, null);
    }

    public static function support(User $owner): self
    {
        return new self(null, $owner);
    }

    /** Resolves the {kind}/{id} pair used by the JSON routes. */
    public static function find(string $kind, int $id): self
    {
        return $kind === 'rooms' ? self::room(Room::findOrFail($id)) : self::support(User::findOrFail($id));
    }

    public function allows(User $user): bool
    {
        return $this->room ? Chat::canChat($user, $this->room) : Chat::canSupport($user, $this->owner);
    }

    public function messages(): Builder
    {
        return $this->room
            ? Message::where('room_id', $this->room->id)
            : Message::where('support_user_id', $this->owner->id);
    }

    /** @return array{room_id: ?int, support_user_id: ?int} */
    public function keys(): array
    {
        return ['room_id' => $this->room?->id, 'support_user_id' => $this->owner?->id];
    }

    public function kind(): string
    {
        return $this->room ? 'rooms' : 'support';
    }

    public function id(): int
    {
        return $this->room?->id ?? $this->owner->id;
    }

    public function channel(): string
    {
        return $this->room ? 'chat.room.'.$this->room->id : 'chat.support.'.$this->owner->id;
    }

    public function url(): string
    {
        return $this->room ? route('chat.room', $this->room) : route('chat.support', $this->owner);
    }

    public function is(?self $other): bool
    {
        return $other !== null && $other->kind() === $this->kind() && $other->id() === $this->id();
    }
}
