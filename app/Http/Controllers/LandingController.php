<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Sport;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Public landing page: sport selector + already-created rooms (events).
     */
    public function index(Request $request): View
    {
        $selectedSport = $request->integer('sport') ?: null;

        return view('landing', [
            'sports' => $this->sports(),
            'selectedSport' => $selectedSport,
            'events' => $this->rooms($request, $selectedSport),
            'filters' => $this->filterValues($request),
        ]);
    }

    /**
     * Rooms partial only — used for AJAX filtering (sport + date/time/city).
     */
    public function roomsPartial(Request $request): View
    {
        $selectedSport = $request->integer('sport') ?: null;

        return view('landing.partials.rooms', [
            'events' => $this->rooms($request, $selectedSport),
            'selectedSport' => $selectedSport,
        ]);
    }

    /**
     * The current filter values, echoed back into the form.
     *
     * @return array<string, string|null>
     */
    private function filterValues(Request $request): array
    {
        return [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'time_from' => $request->input('time_from'),
            'city' => $request->input('city'),
        ];
    }

    /**
     * Sports with a count of their upcoming rooms, for the header selector.
     */
    private function sports()
    {
        return Sport::query()
            ->withCount(['events' => fn (Builder $q) => $q->where('start_time', '>=', now())])
            ->orderBy('name')
            ->get();
    }

    /**
     * Upcoming rooms (events), filtered by sport + date range / time / city.
     */
    private function rooms(Request $request, ?int $sportId)
    {
        $city = trim((string) $request->input('city'));

        return Event::query()
            ->upcoming()
            ->forSport($sportId)
            ->inCity($city !== '' ? $city : null)
            ->when($request->input('date_from'), fn (Builder $q, $date) => $q->whereDate('start_time', '>=', $date))
            ->when($request->input('date_to'), fn (Builder $q, $date) => $q->whereDate('start_time', '<=', $date))
            ->when($request->input('time_from'), fn (Builder $q, $time) => $q->whereTime('start_time', '>=', $time))
            ->with(['sport', 'venue', 'creator'])
            ->withCount('participants')
            ->orderBy('start_time')
            ->get();
    }
}
