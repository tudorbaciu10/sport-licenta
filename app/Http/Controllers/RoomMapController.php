<?php

namespace App\Http\Controllers;

use App\Enums\RoomStatus;
use App\Http\Requests\RoomMapRequest;
use App\Models\Room;
use Illuminate\Http\JsonResponse;

/**
 * Pins for the phone map on /rooms?view=map: every upcoming open match that still needs players
 * and has a pin, from the whole country. Read-only; only what the pin and its card show.
 */
class RoomMapController extends Controller
{
    /** Enough for the whole country; the soonest matches win when there are more. */
    private const LIMIT = 500;

    public function __invoke(RoomMapRequest $request): JsonResponse
    {
        $rooms = Room::query()
            ->upcoming()
            ->filter($request->validated())
            ->where('status', RoomStatus::Open)
            ->whereColumn('current_players_count', '<', 'max_players')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['sport', 'city'])
            ->orderBy('match_date_time')
            ->limit(self::LIMIT + 1)
            ->get();

        $locale = app()->getLocale();

        return response()->json([
            'rooms' => $rooms->take(self::LIMIT)->map(function (Room $room) use ($locale) {
                $when = $room->match_date_time->locale($locale);

                return [
                    'id' => $room->id,
                    'url' => route('rooms.show', $room),
                    'title' => $room->title,
                    'sport' => [
                        'name' => $room->sport->label(),
                        'slug' => $room->sport->slug,
                        'var' => $room->sport->cssVar(),
                        'icon' => $room->sport->icon(),
                    ],
                    'starts_at' => $room->match_date_time->toIso8601String(),
                    'day' => $when->isoFormat('ddd, D MMM'),
                    'time' => $when->format('H:i'),
                    'location' => $room->location_name,
                    'city' => $room->city->label(),
                    'price' => (int) $room->price,
                    'spots_left' => $room->spotsLeft(),
                    'availability' => $room->availability(),
                    'lat' => $room->latitude,
                    'lng' => $room->longitude,
                ];
            })->values(),
            'truncated' => $rooms->count() > self::LIMIT,
        ]);
    }
}
