<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventParticipationController extends Controller
{
    /**
     * Join an event, enforcing the participation guards.
     */
    public function store(Request $request, Event $event): Response
    {
        $user = $request->user();

        if ($event->status !== Event::STATUS_OPEN) {
            return $this->respond($request, $event, 'error', __('This event is not open for joining.'));
        }

        if ($event->user_id === $user->id) {
            return $this->respond($request, $event, 'error', __('You cannot join an event you created.'));
        }

        if ($event->hasParticipant($user)) {
            return $this->respond($request, $event, 'error', __('You have already joined this event.'));
        }

        if ($event->isFull()) {
            return $this->respond($request, $event, 'error', __('This event is already full.'));
        }

        $event->participants()->attach($user->id, ['status' => 'joined']);

        // Flip to "full" once capacity is reached so it drops out of "open" listings.
        if ($event->isFull()) {
            $event->update(['status' => Event::STATUS_FULL]);
        }

        return $this->respond($request, $event, 'status', __('You joined the event.'));
    }

    /**
     * Leave an event; reopen it if it had been marked full.
     */
    public function destroy(Request $request, Event $event): Response
    {
        $user = $request->user();

        if (! $event->hasParticipant($user)) {
            return $this->respond($request, $event, 'error', __('You are not part of this event.'));
        }

        $event->participants()->detach($user->id);

        if ($event->status === Event::STATUS_FULL) {
            $event->update(['status' => Event::STATUS_OPEN]);
        }

        return $this->respond($request, $event, 'status', __('You left the event.'));
    }

    /**
     * AJAX (slide-over) → re-rendered event detail partial; classic page → redirect back.
     */
    private function respond(Request $request, Event $event, string $type, string $message): Response
    {
        if ($request->ajax()) {
            $event->load(['sport', 'venue', 'creator', 'participants']);

            return response()->view('landing.partials.room-detail', [
                'event' => $event,
                'flash' => $message,
                'flashType' => $type,
            ]);
        }

        return back()->with($type, $message);
    }
}
