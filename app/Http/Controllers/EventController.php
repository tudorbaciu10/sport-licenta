<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use App\Models\Sport;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
     * Event creation form as a slide-over partial (unified landing surface).
     */
    public function createForm(): View
    {
        return view('landing.partials.room-form', [
            'sports' => Sport::orderBy('name')->get(),
            'venues' => Venue::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a new event created by the current user.
     *
     * Handles both the classic Breeze page (redirect) and the slide-over
     * panel (AJAX: HTML partial back — the form with errors, or a success marker).
     */
    public function store(Request $request)
    {
        $rules = (new StoreEventRequest())->rules();

        if ($request->ajax()) {
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->view('landing.partials.room-form', [
                    'sports' => Sport::orderBy('name')->get(),
                    'venues' => Venue::orderBy('name')->get(),
                    'errorsBag' => $validator->errors(),
                    'values' => $request->all(),
                ], 422);
            }

            $request->user()->createdEvents()->create($validator->validated());

            return view('landing.partials.form-success', [
                'message' => __('landing.panel_event_created'),
                'refresh' => 'rooms',
            ]);
        }

        $data = $request->validate($rules);
        $event = $request->user()->createdEvents()->create($data);

        return redirect()
            ->route('events.show', $event)
            ->with('status', __('Event created.'));
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
