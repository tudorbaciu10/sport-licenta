<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Pushed to everyone in the conversation through Reverb right away (no queue worker needed).
 * Support messages also ping the support team's inbox.
 */
class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public Message $message) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        if ($this->message->room_id) {
            return [new PrivateChannel('chat.room.'.$this->message->room_id)];
        }

        return [
            new PrivateChannel('chat.support.'.$this->message->support_user_id),
            new PrivateChannel('chat.support.inbox'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return $this->message->toChat() + ['thread' => $this->message->support_user_id];
    }
}
