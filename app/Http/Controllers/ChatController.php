<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use App\Support\Chat;
use App\Support\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * /chat: the list of conversations (match chats + support) and the open one.
 * The page loads the latest messages; new ones arrive over Reverb, or by polling
 * the JSON routes when the WebSocket is down.
 */
class ChatController extends Controller
{
    /** Messages drawn with the page and per "older messages" request. */
    private const PAGE = 50;

    public function index(Request $request): View
    {
        return view('chat.show', $this->sidebar($request->user(), null) + ['conversation' => null]);
    }

    public function room(Request $request, Room $room): View
    {
        return $this->show($request->user(), Conversation::room($room));
    }

    public function support(Request $request, ?User $owner = null): View|RedirectResponse
    {
        $user = $request->user();

        // The support team answers others; it has no thread of its own.
        if (! $owner && $user->is_support) {
            return redirect()->route('chat.index');
        }

        return $this->show($user, Conversation::support($owner ?? $user));
    }

    /** ?after=ID → newer messages (polling); ?before=ID → older page. */
    public function messages(Request $request, string $kind, int $id): JsonResponse
    {
        $conversation = $this->authorized($request->user(), $kind, $id);
        $data = $request->validate(['after' => ['nullable', 'integer', 'min:0'], 'before' => ['nullable', 'integer', 'min:1']]);

        $messages = isset($data['before'])
            ? $conversation->messages()->where('id', '<', $data['before'])->latest('id')->limit(self::PAGE)->with('author')->get()->reverse()->values()
            : $conversation->messages()->where('id', '>', $data['after'] ?? 0)->orderBy('id')->limit(100)->with('author')->get();

        return response()->json(['messages' => $messages->map->toChat()]);
    }

    public function send(Request $request, string $kind, int $id): JsonResponse
    {
        $user = $request->user();
        $conversation = $this->authorized($user, $kind, $id);

        $request->merge(['body' => trim((string) $request->input('body'))]);
        $data = $request->validate(['body' => ['required', 'string', 'max:'.Message::MAX_LENGTH]]);

        $message = Message::create($conversation->keys() + ['user_id' => $user->id, 'body' => $data['body']]);
        $message->setRelation('author', $user);
        Chat::markRead($user, $conversation->room, $conversation->owner, $message->id);

        // Saved either way; if Reverb is not running the others get it from polling.
        rescue(function () use ($message) {
            broadcast(new MessageSent($message))->toOthers();
        });

        return response()->json(['message' => $message->toChat()], 201);
    }

    public function read(Request $request, string $kind, int $id): JsonResponse
    {
        $user = $request->user();
        $conversation = $this->authorized($user, $kind, $id);
        $last = (int) $conversation->messages()->where('id', '<=', (int) $request->input('id'))->max('id');

        if ($last) {
            Chat::markRead($user, $conversation->room, $conversation->owner, $last);
        }

        return response()->json(['ok' => true]);
    }

    private function show(User $user, Conversation $conversation): View
    {
        abort_unless($conversation->allows($user), 403);

        $messages = $conversation->messages()->latest('id')->limit(self::PAGE)->with('author')->get()->reverse()->values();
        if ($messages->isNotEmpty()) {
            Chat::markRead($user, $conversation->room, $conversation->owner, $messages->last()->id);
        }

        $room = $conversation->room?->load(['sport', 'players.sports']);

        return view('chat.show', $this->sidebar($user, $conversation) + [
            'conversation' => $conversation,
            'room' => $room,
            'messages' => $messages->map->toChat(),
            'hasOlder' => $messages->count() === self::PAGE,
        ]);
    }

    private function authorized(User $user, string $kind, int $id): Conversation
    {
        $conversation = Conversation::find($kind, $id);
        abort_unless($conversation->allows($user), 403);

        return $conversation;
    }

    /**
     * The conversation list: the user's match chats, then support (their own thread,
     * or every user's thread for the support team), each with its last message and unread count.
     *
     * @return array{rooms: Collection, threads: Collection}
     */
    private function sidebar(User $user, ?Conversation $open): array
    {
        $rooms = Chat::rooms($user);
        $roomUnread = Chat::unreadByRoom($user, $rooms->pluck('id'));
        $threadUnread = Chat::unreadBySupportThread($user);

        $lastByRoom = $this->lastMessages('room_id', $rooms->pluck('id'));

        $owners = $user->is_support
            ? User::whereIn('id', Message::whereNotNull('support_user_id')->distinct()->pluck('support_user_id'))->get()
            : collect([$user]);
        $lastByThread = $this->lastMessages('support_user_id', $owners->pluck('id'));

        $item = fn (Conversation $c, ?Message $last, int $unread, string $title) => [
            'conversation' => $c,
            'title' => $title,
            'last' => $last,
            'unread' => $open && $c->is($open) ? 0 : $unread,
            'active' => $c->is($open),
        ];

        return [
            'rooms' => $rooms->map(fn (Room $room) => $item(Conversation::room($room), $lastByRoom->get($room->id), $roomUnread->get($room->id, 0), $room->title) + ['room' => $room]),
            'threads' => $owners
                ->map(fn (User $owner) => $item(Conversation::support($owner), $lastByThread->get($owner->id), $threadUnread->get($owner->id, 0), $user->is_support ? $owner->name : __('chat.support')) + ['owner' => $owner])
                ->sortByDesc(fn ($t) => $t['last']?->id ?? 0)
                ->values(),
        ];
    }

    /** Latest message per conversation, keyed by room_id / support_user_id. */
    private function lastMessages(string $column, Collection $ids): Collection
    {
        if ($ids->isEmpty()) {
            return collect();
        }

        return Message::whereIn('id', Message::selectRaw('max(id)')->whereIn($column, $ids)->groupBy($column))
            ->with('author')
            ->get()
            ->keyBy($column);
    }
}
