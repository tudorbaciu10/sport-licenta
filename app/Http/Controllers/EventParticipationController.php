<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class EventParticipationController extends Controller
{
    /**
     * Join an event, enforcing the participation guards.
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        $user = $request->user();

        if ($event->status !== Event::STATUS_OPEN) {
            return back()->with('error', 'This event is not open for joining.');
        }

        if ($event->user_id === $user->id) {
            return back()->with('error', 'You cannot join an event you created.');
        }

        if ($event->hasParticipant($user)) {
            return back()->with('error', 'You have already joined this event.');
        }

        if ($event->isFull()) {
            return back()->with('error', 'This event is already full.');
        }

        $event->participants()->attach($user->id, ['status' => 'joined']);

        // Flip to "full" once capacity is reached so it drops out of "open" listings.
        if ($event->isFull()) {
            $event->update(['status' => Event::STATUS_FULL]);
        }

        return back()->with('status', 'You joined the event.');
    }

    /**
     * Leave an event; reopen it if it had been marked full.
     */
    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $user = $request->user();

        if (! $event->hasParticipant($user)) {
            return back()->with('error', 'You are not part of this event.');
        }

        $event->participants()->detach($user->id);

        if ($event->status === Event::STATUS_FULL) {
            $event->update(['status' => Event::STATUS_OPEN]);
        }

        return back()->with('status', 'You left the event.');
    }
}
