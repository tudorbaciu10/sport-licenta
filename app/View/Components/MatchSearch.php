<?php

namespace App\View\Components;

use App\Models\City;
use App\Models\Room;
use App\Models\Sport;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Landing-page search bar: text + date + time, with venue / free-spot pills.
 * Submits to /rooms, which understands q, date, time, venue, free, city and sport.
 */
class MatchSearch extends Component
{
    public function render(): View
    {
        $upcoming = Room::query()->upcoming();
        $today = (clone $upcoming)->whereDate('match_date_time', today())->count();

        return view('components.match-search', [
            'payload' => [
                'cities' => City::orderBy('name')->get(['name', 'slug'])->toArray(),
                'sports' => Sport::orderBy('name')->get(['name', 'slug'])->toArray(),
                'today' => today()->toDateString(),
                'action' => route('rooms.index'),
            ],
            'venueTypes' => Room::VENUE_TYPES,
            // "0 azi" reads as broken, so fall back to all open matches when today is empty.
            'liveLabel' => $today ? "{$today} meciuri azi" : $upcoming->count().' meciuri deschise',
        ]);
    }
}
