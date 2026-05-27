<?php

namespace App\Events;

use App\Events\Concerns\FormatsChatBroadcastPayload;
use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable;
    use FormatsChatBroadcastPayload;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public ChatMessage $message)
    {
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel('chat.conversation.'.$this->message->ConversationId)];
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->formatMessage($this->message),
        ];
    }
}
