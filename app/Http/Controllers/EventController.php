<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use App\Models\Sport;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * List upcoming events with basic filtering (sport, city, date).
     */
    public function index(Request $request): View
    {
        $events = Event::query()
            ->upcoming()
            ->forSport($request->integer('sport') ?: null)
            ->inCity($request->string('city')->trim()->value() ?: null)
            ->fromDate($request->input('date') ?: null)
            ->with(['sport', 'venue', 'creator'])
            ->withCount('participants')
            ->orderBy('start_time')
            ->paginate(12)
            ->withQueryString();

        return view('events.index', [
            'events' => $events,
            'sports' => Sport::orderBy('name')->get(),
            'filters' => $request->only(['sport', 'city', 'date']),
        ]);
    }

    /**
     * Show the event creation form.
     */
    public function create(): View
    {
        return view('events.create', [
            'sports' => Sport::orderBy('name')->get(),
            'venues' => Venue::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a new event created by the current user.
     */
    public function store(StoreEventRequest $request)
    {
        $event = $request->user()->createdEvents()->create($request->validated());

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Event created.');
    }

    /**
     * Show a single event.
     */
    public function show(Event $event): View
    {
        $event->load(['sport', 'venue', 'creator', 'participants']);

        return view('events.show', [
            'event' => $event,
        ]);
    }
}
