<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Room;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Home: "Ce joci azi?" with real counts for one city (docs/DESIGN.md §6.1, §6.7). */
class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $cities = City::orderBy('name')->get();

        // City from the link, else the player's own, else Chișinău (launch city), else all.
        $city = $cities->firstWhere('slug', $request->query('city'))
            ?? $request->user()?->city
            ?? $cities->firstWhere('slug', 'chisinau');

        $inCity = fn ($q) => $q->upcoming()->when($city, fn ($q) => $q->where('city_id', $city->id));

        return view('home', [
            'cities' => $cities,
            'city' => $city,
            'sports' => Sport::withCount(['rooms as open_count' => $inCity])->orderBy('name')->get(),
            'upcoming' => Room::query()->tap($inCity)->where('status', 'open')
                ->with(['sport', 'city'])->orderBy('match_date_time')->limit(6)->get()
        ]);
    }
}
