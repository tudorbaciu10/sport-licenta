<?php

namespace App\Http\Controllers;

use App\Enums\RoomStatus;
use App\Http\Requests\CalendarDayRequest;
use App\Http\Requests\CalendarMonthRequest;
use App\Models\City;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * JSON for <x-calendar>. Only upcoming matches that are open or full.
 * Guests get public fields; a logged-in user also gets their role, equipment,
 * players and the join / interest / leave actions.
 */
class CalendarController extends Controller
{
    /** Days of one month that have matches: count, sports and whether one of them is mine. */
    public function days(CalendarMonthRequest $request): JsonResponse
    {
        $month = Carbon::createFromFormat('Y-m-d', $request->validated('month').'-01')->startOfDay();
        $from = $month->copy()->max(now());
        $to = $month->copy()->endOfMonth();
        $user = $request->user();

        $rows = $to->isPast() ? collect() : $this->scope($request->validated('city'), $request->boolean('mine') ? $user : null)
            ->join('sports', 'sports.id', '=', 'rooms.sport_id')
            ->whereBetween('rooms.match_date_time', [$from, $to])
            ->select('rooms.match_date_time', 'sports.slug as sport')
            ->when($user, fn ($q) => $q->selectRaw(
                'exists(select 1 from room_user where room_user.room_id = rooms.id and room_user.user_id = ?) as mine',
                [$user->id]
            ))
            ->orderBy('rooms.match_date_time')
            ->toBase()
            ->get();   // one query for the whole month

        $days = $rows->groupBy(fn ($row) => Carbon::parse($row->match_date_time)->toDateString())
            ->map(fn ($dayRows, $date) => [
                'date' => $date,
                'count' => $dayRows->count(),
                // Most frequent sport first, so the dots show what is played most that day.
                'sports' => $dayRows->pluck('sport')->countBy()->sortDesc()->keys()->values(),
                'mine' => $dayRows->contains(fn ($row) => (bool) ($row->mine ?? false)),
            ])
            ->values();

        return response()->json(['month' => $month->format('Y-m'), 'days' => $days]);
    }

    /** Matches of one day, ordered by time. */
    public function day(CalendarDayRequest $request): JsonResponse
    {
        $date = Carbon::createFromFormat('Y-m-d', $request->validated('date'))->startOfDay();
        $user = $request->user();

        $rooms = $this->scope($request->validated('city'), $request->boolean('mine') ? $user : null)
            ->whereBetween('match_date_time', [$date->copy()->max(now()), $date->copy()->endOfDay()])
            ->with(['sport', 'city'])
            ->when($user, fn ($q) => $q->with('participants'))
            ->orderBy('match_date_time')
            ->get();

        return response()->json([
            'date' => $date->toDateString(),
            'rooms' => $rooms->map(fn (Room $room) => $this->present($room, $user))->values(),
        ]);
    }

    /** Upcoming, open or full, optionally in one city and/or only the user's matches. */
    private function scope(?string $citySlug, ?User $onlyFor): Builder
    {
        return Room::query()
            ->whereIn('rooms.status', [RoomStatus::Open, RoomStatus::Full])
            ->where('rooms.match_date_time', '>', now())
            ->when($citySlug, fn ($q) => $q->whereIn('rooms.city_id', City::select('id')->where('slug', $citySlug)))
            ->when($onlyFor, fn ($q) => $q->whereExists(fn ($sub) => $sub->select(DB::raw(1))->from('room_user')
                ->whereColumn('room_user.room_id', 'rooms.id')
                ->where('room_user.user_id', $onlyFor->id)));
    }

    private function present(Room $room, ?User $user): array
    {
        $left = $room->spotsLeft();
        $data = [
            'id' => $room->id,
            'url' => route('rooms.show', $room),
            'title' => $room->title,
            'time' => $room->match_date_time->format('H:i'),
            'sport' => ['slug' => $room->sport->slug, 'name' => $room->sport->label(), 'var' => $room->sport->cssVar()],
            'location' => $room->location_name.', '.$room->city->label(),
            'venue' => $room->venue_type ? __('rooms.index.'.$room->venue_type) : null,
            'max_players' => $room->max_players,
            'current_players_count' => $room->current_players_count,
            'spots' => __('components.match.taken', ['taken' => $room->current_players_count, 'max' => $room->max_players]),
            'spots_left' => trans_choice('components.match.spots_left', $left, ['count' => $left]),
            'status' => $room->status->value,
            'availability' => $room->availability(),
            'availability_label' => __('components.badge.'.$room->availability()),
            'price' => $room->price ? __('match.price.amount', ['price' => $room->price]) : __('match.price.free'),
        ];

        if (! $user) {
            return $data;   // guests: no personal data about players
        }

        // Logged-in extras: my role, money and equipment, who plays, quick actions.
        $role = $room->user_id === $user->id ? 'organizer' : $room->participants->firstWhere('id', $user->id)?->pivot->status;
        $players = $room->participants->where('pivot.status', 'joined');

        return $data + [
            'my_role' => $role,
            'my_role_label' => $role ? __('profile.matches.role.'.$role) : null,
            'price_detail' => $room->price && $room->price_collector ? __('match.price.collector.'.$room->price_collector) : null,
            'equipment' => $room->equipment_by ? __('match.equipment.'.$room->equipment_by) : null,
            'players' => $players->take(5)->map(fn (User $p) => [
                'name' => $p->name,
                'initials' => self::initials($p->name),
                'avatar' => $p->avatarUrl(),
            ])->values(),
            'players_more' => max(0, $players->count() - 5),
            'actions' => [
                'join' => in_array($role, [null, 'interested'], true) && $room->isJoinable() && $left > 0 ? route('rooms.join', $room) : null,
                'interest' => $role === null ? route('rooms.interest', $room) : null,
                'leave' => in_array($role, ['joined', 'interested'], true) ? route('rooms.leave', $room) : null,
                'leave_label' => $role === 'interested' ? __('match.show.uninterest') : __('match.show.leave'),
            ],
        ];
    }

    private static function initials(string $name): string
    {
        $words = array_filter(preg_split('/\s+/u', trim($name)) ?: []);

        return mb_strtoupper(implode('', array_map(fn ($w) => mb_substr($w, 0, 1), array_slice($words, 0, 2))));
    }
}
