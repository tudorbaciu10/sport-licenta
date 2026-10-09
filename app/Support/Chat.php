<?php

namespace App\Support;

use App\Enums\ParticipationStatus;
use App\Enums\RoomStatus;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Who may talk where. A match chat opens once the user has joined a match that has
 * at least one other player; only joined players (the organiser is one) can read or write.
 * Every user has one private thread with the support team (users with is_support).
 */
class Chat
{
    /** Players needed before a match chat opens: you + at least one more. */
    public const MIN_PLAYERS = 2;

    /** Chats of finished matches stay open this long (to settle up, share photos…). */
    public const KEEP_DAYS = 7;

    /** @var \WeakMap<User, Collection<int, Room>>|null remembered per user object, i.e. per request (the nav asks on every page) */
    private static ?\WeakMap $rooms = null;

    /** Matches whose chat the user can open, soonest first. */
    public static function rooms(User $user): Collection
    {
        self::$rooms ??= new \WeakMap;

        return self::$rooms[$user] ??= self::roomsQuery($user)->with('sport')->orderBy('match_date_time')->get();
    }

    public static function canChat(User $user, Room $room): bool
    {
        return self::roomsQuery($user)->whereKey($room->id)->exists();
    }

    /** The owner of a support thread and the support team can read and write in it. */
    public static function canSupport(User $user, User $owner): bool
    {
        return $user->is_support || $user->id === $owner->id;
    }

    /** Show "Chat" in the navigation? */
    public static function available(?User $user): bool
    {
        return $user !== null && ($user->is_support || self::rooms($user)->isNotEmpty());
    }

    /** Unread messages per room id for the given rooms, written by others. */
    public static function unreadByRoom(User $user, Collection $roomIds): Collection
    {
        if ($roomIds->isEmpty()) {
            return collect();
        }

        return Message::query()
            ->leftJoin('chat_reads as r', fn ($j) => $j->on('r.room_id', '=', 'messages.room_id')->where('r.user_id', $user->id))
            ->whereIn('messages.room_id', $roomIds)
            ->where('messages.user_id', '!=', $user->id)
            ->whereColumn('messages.id', '>', DB::raw('coalesce(r.last_read_message_id, 0)'))
            ->groupBy('messages.room_id')
            ->pluck(DB::raw('count(*)'), 'messages.room_id')
            ->map(fn ($n) => (int) $n);
    }

    /**
     * Unread replies in support threads, per thread owner. For a regular user that is
     * their own thread; for the support team it is every user's thread.
     */
    public static function unreadBySupportThread(User $user): Collection
    {
        return Message::query()
            ->leftJoin('chat_reads as r', fn ($j) => $j->on('r.support_user_id', '=', 'messages.support_user_id')->where('r.user_id', $user->id))
            ->whereNotNull('messages.support_user_id')
            ->when(! $user->is_support, fn ($q) => $q->where('messages.support_user_id', $user->id))
            ->where('messages.user_id', '!=', $user->id)
            ->whereColumn('messages.id', '>', DB::raw('coalesce(r.last_read_message_id, 0)'))
            ->groupBy('messages.support_user_id')
            ->pluck(DB::raw('count(*)'), 'messages.support_user_id')
            ->map(fn ($n) => (int) $n);
    }

    /** Badge number in the navigation. */
    public static function unreadTotal(User $user): int
    {
        return self::unreadByRoom($user, self::rooms($user)->pluck('id'))->sum()
            + self::unreadBySupportThread($user)->sum();
    }

    /** Remember that the user has seen everything up to this message. */
    public static function markRead(User $user, ?Room $room, ?User $supportOwner, int $messageId): void
    {
        $keys = ['user_id' => $user->id, 'room_id' => $room?->id, 'support_user_id' => $supportOwner?->id];
        $current = (int) DB::table('chat_reads')->where($keys)->value('last_read_message_id');

        if ($messageId > $current) {
            DB::table('chat_reads')->updateOrInsert($keys, ['last_read_message_id' => $messageId, 'updated_at' => now(), 'created_at' => now()]);
        }
    }


    private static function roomsQuery(User $user): Builder
    {
        return Room::query()
            ->whereHas('participants', fn ($q) => $q->whereKey($user->id)->where('room_user.status', ParticipationStatus::Joined->value))
            ->where('current_players_count', '>=', self::MIN_PLAYERS)
            ->where('status', '!=', RoomStatus::Cancelled)
            ->where('match_date_time', '>=', now()->subDays(self::KEEP_DAYS));
    }
}
