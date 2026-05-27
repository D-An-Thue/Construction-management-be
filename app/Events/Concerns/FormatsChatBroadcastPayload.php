<?php

namespace App\Events\Concerns;

use App\Models\ChatMessage;
use App\Models\Person;
use DateTimeInterface;

trait FormatsChatBroadcastPayload
{
    protected function formatMessage(ChatMessage $message): array
    {
        return [
            'id' => $message->Id,
            'conversationId' => $message->ConversationId,
            'senderPersonId' => $message->SenderPersonId,
            'messageType' => $message->MessageType,
            'body' => $message->Body,
            'metadata' => $message->Metadata,
            'replyToMessageId' => $message->ReplyToMessageId,
            'editedAt' => $this->formatDateTime($message->EditedAt),
            'createdAt' => $this->formatDateTime($message->CreatedAt),
            'updatedAt' => $this->formatDateTime($message->UpdatedAt),
            'deletedAt' => $this->formatDateTime($message->DeleteAt),
            'sender' => $message->sender ? $this->formatPerson($message->sender) : null,
            'replyTo' => $message->replyTo ? [
                'id' => $message->replyTo->Id,
                'conversationId' => $message->replyTo->ConversationId,
                'senderPersonId' => $message->replyTo->SenderPersonId,
                'body' => $message->replyTo->Body,
                'createdAt' => $this->formatDateTime($message->replyTo->CreatedAt),
                'sender' => $message->replyTo->sender ? $this->formatPerson($message->replyTo->sender) : null,
            ] : null,
        ];
    }

    protected function formatPerson(Person $person): array
    {
        return [
            'id' => $person->Id,
            'name' => $person->Name,
            'avatarUrl' => $person->AvatarUrl,
        ];
    }

    protected function formatDateTime(?DateTimeInterface $value): ?string
    {
        return $value?->format('Y-m-d H:i:s');
    }
}
