<?php

namespace App\Events;

use App\Events\Concerns\FormatsChatBroadcastPayload;
use App\Models\Person;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ChatConversationRead implements ShouldBroadcastNow
{
    use Dispatchable;
    use FormatsChatBroadcastPayload;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public int $conversationId,
        public Person $person,
        public int $messageId,
        public ?Carbon $readAt
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel('chat.conversation.'.$this->conversationId)];
    }

    public function broadcastAs(): string
    {
        return 'conversation.read';
    }

    public function broadcastWith(): array
    {
        return [
            'conversationId' => $this->conversationId,
            'messageId' => $this->messageId,
            'personId' => $this->person->Id,
            'readAt' => $this->formatDateTime($this->readAt),
            'person' => $this->formatPerson($this->person),
        ];
    }
}
