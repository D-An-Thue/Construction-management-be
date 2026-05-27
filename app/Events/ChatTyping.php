<?php

namespace App\Events;

use App\Events\Concerns\FormatsChatBroadcastPayload;
use App\Models\Person;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatTyping implements ShouldBroadcastNow
{
    use Dispatchable;
    use FormatsChatBroadcastPayload;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public int $conversationId,
        public Person $person,
        public bool $isTyping
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel('chat.conversation.'.$this->conversationId)];
    }

    public function broadcastAs(): string
    {
        return 'conversation.typing';
    }

    public function broadcastWith(): array
    {
        return [
            'conversationId' => $this->conversationId,
            'personId' => $this->person->Id,
            'isTyping' => $this->isTyping,
            'person' => $this->formatPerson($this->person),
        ];
    }
}
