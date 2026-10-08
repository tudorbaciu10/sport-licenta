<?php

namespace App\Services;

use App\Enums\ParticipationStatus;
use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * All changes to a room's roster go through here, inside a transaction with
 * the room row locked, so current_players_count can't drift when two people
 * grab the last spot at the same time.
 */
class RoomMembership
{
    public function create(User $creator, array $data): Room
    {
        return DB::transaction(function () use ($creator, $data) {
            $room = $creator->createdRooms()->create($data);
            $room->participants()->attach($creator->id, ['status' => ParticipationStatus::Joined->value]);
            $room->forceFill(['current_players_count' => 1])->save();

            return $room;
        });
    }

    public function join(Room $room, User $user): void
    {
        DB::transaction(function () use ($room, $user) {
            $room = Room::lockForUpdate()->findOrFail($room->id);

            if ($this->statusOf($room, $user) === ParticipationStatus::Joined) {
                return;
            }
            if (! $room->isJoinable() || $room->spotsLeft() === 0) {
                throw ValidationException::withMessages(['room' => __('match.flash.full')]);
            }

            $room->participants()->syncWithoutDetaching([$user->id => ['status' => ParticipationStatus::Joined->value]]);
            $room->current_players_count++;
            $room->status = $room->spotsLeft() === 0 ? RoomStatus::Full : RoomStatus::Open;
            $room->save();
        });
    }

    public function markInterested(Room $room, User $user): void
    {
        DB::transaction(function () use ($room, $user) {
            $room = Room::lockForUpdate()->findOrFail($room->id);

            if ($this->statusOf($room, $user) !== null) {
                return;
            }
            if (! in_array($room->status, [RoomStatus::Open, RoomStatus::Full], true)) {
                throw ValidationException::withMessages(['room' => __('match.flash.inactive')]);
            }

            $room->participants()->attach($user->id, ['status' => ParticipationStatus::Interested->value]);
        });
    }

    public function leave(Room $room, User $user): void
    {
        if ($room->user_id === $user->id) {
            throw ValidationException::withMessages(['room' => __('match.flash.organizer_leave')]);
        }

        DB::transaction(function () use ($room, $user) {
            $room = Room::lockForUpdate()->findOrFail($room->id);
            $current = $this->statusOf($room, $user);

            if ($current === null) {
                return;
            }

            $room->participants()->detach($user->id);

            if ($current === ParticipationStatus::Joined) {
                $room->current_players_count = max(0, $room->current_players_count - 1);
                if ($room->status === RoomStatus::Full) {
                    $room->status = RoomStatus::Open;
                }
                $room->save();
            }
        });
    }

    private function statusOf(Room $room, User $user): ?ParticipationStatus
    {
        $status = $room->participants()->whereKey($user->id)->value('room_user.status');

        return $status ? ParticipationStatus::from($status) : null;
    }
}
