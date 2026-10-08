<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Models\City;
use App\Models\Room;
use App\Models\Sport;
use App\Services\RoomMembership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function __construct(private RoomMembership $membership) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'city' => ['nullable', 'string', 'exists:cities,slug'],
            'sport' => ['nullable', 'string', 'exists:sports,slug'],
            'date' => ['nullable', 'date'],
            'time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', Rule::in(array_keys(Room::VENUE_TYPES))],
            'free' => ['nullable', 'boolean'],
            'q' => ['nullable', 'string', 'max:80'],
            'when' => ['nullable', Rule::in(['weekend'])],
            'price' => ['nullable', Rule::in(['free'])],
        ]);

        // Sport cards show how many upcoming rooms each sport has in the chosen city.
        $sports = Sport::query()
            ->withCount(['rooms as upcoming_count' => fn ($q) => $q->upcoming()
                ->filter(['city' => $filters['city'] ?? null])])
            ->orderBy('name')
            ->get();

        $rooms = Room::query()
            ->upcoming()
            ->filter($filters)
            ->with(['sport', 'city', 'creator'])
            ->orderBy('match_date_time')
            ->paginate(12)
            ->withQueryString();

        return view('rooms.index', [
            'rooms' => $rooms,
            'sports' => $sports,
            'cities' => City::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        return view('rooms.create', [
            'sports' => Sport::orderBy('name')->get(),
            'cities' => City::orderBy('name')->get(),
            'selectedSport' => Sport::where('slug', $request->query('sport'))->value('id'),
        ]);
    }

    public function store(StoreRoomRequest $request): RedirectResponse
    {
        $room = $this->membership->create($request->user(), $request->validated());

        return redirect()->route('rooms.show', $room)->with('status', __('match.flash.created'));
    }

    public function show(Request $request, Room $room): View
    {
        // A guest who taps "Ocupă un loc" logs in and comes straight back to this match.
        if (! $request->user()) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        $room->load(['sport', 'city', 'creator', 'participants']);

        return view('rooms.show', [
            'room' => $room,
            'myStatus' => $room->participationOf(auth()->user()),
        ]);
    }

    public function join(Request $request, Room $room): RedirectResponse
    {
        $this->membership->join($room, $request->user());

        return back()->with('status', __('match.flash.joined'));
    }

    public function interest(Request $request, Room $room): RedirectResponse
    {
        $this->membership->markInterested($room, $request->user());

        return back()->with('status', __('match.flash.interested'));
    }

    public function leave(Request $request, Room $room): RedirectResponse
    {
        $this->membership->leave($room, $request->user());

        return back()->with('status', __('match.flash.left'));
    }
}
