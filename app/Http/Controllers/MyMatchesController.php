<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/** "Meciurile mele": matches the user organises, joined or marked as interesting. */
class MyMatchesController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'past' ? 'past' : 'upcoming';

        $rooms = $request->user()->rooms()
            ->with(['sport', 'city'])
            ->where('match_date_time', $tab === 'past' ? '<' : '>=', now())
            ->orderBy('match_date_time', $tab === 'past' ? 'desc' : 'asc')
            ->paginate(12)
            ->withQueryString();

        return view('my-matches', ['rooms' => $rooms, 'tab' => $tab]);
    }
}
