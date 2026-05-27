<?php

namespace App\Http\Controllers\Api;

use App\Events\ChatMessageDeleted;
use App\Events\ChatMessageSent;
use App\Events\ChatMessageUpdated;
use App\Models\ChatMessage;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatMessageController extends BaseApiController
{
    public function __construct(private readonly ChatService $chatService)
    {
    }

    public function index(int $conversationId, Request $request): JsonResponse
    {
        $actorPersonId = $this->currentUserId();

        if (! $actorPersonId) {
            abort(401, 'Unauthenticated.');
        }

        $validated = $request->validate([
            'beforeId' => ['nullable', 'integer'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $messages = $this->chatService->listMessages(
            $conversationId,
            $actorPersonId,
            isset($validated['beforeId']) ? (int) $validated['beforeId'] : null,
            (int) ($validated['limit'] ?? 30)
        )->map(fn (ChatMessage $message) => $this->mapMessage($message))
            ->values();

        return $this->jsonResponse($messages);
    }

    public function store(int $conversationId, Request $request): JsonResponse
    {
        $actorPersonId = $this->currentUserId();

        if (! $actorPersonId) {
            abort(401, 'Unauthenticated.');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'replyToMessageId' => ['nullable', 'integer'],
            'metadata' => ['nullable', 'array'],
            'messageType' => ['nullable', 'integer'],
        ]);

        $message = $this->chatService->sendMessage($conversationId, $actorPersonId, [
            'Body' => $validated['body'],
            'ReplyToMessageId' => $validated['replyToMessageId'] ?? null,
            'Metadata' => $validated['metadata'] ?? null,
            'MessageType' => $validated['messageType'] ?? ChatMessage::TYPE_TEXT,
        ]);

        event(new ChatMessageSent($message));

        return $this->jsonResponse($this->mapMessage($message));
    }

    public function update(int $messageId, Request $request): JsonResponse
    {
        $actorPersonId = $this->currentUserId();

        if (! $actorPersonId) {
            abort(401, 'Unauthenticated.');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = $this->chatService->editMessage($messageId, $actorPersonId, $validated['body']);

        event(new ChatMessageUpdated($message));

        return $this->jsonResponse($this->mapMessage($message));
    }

    public function destroy(int $messageId): JsonResponse
    {
        $actorPersonId = $this->currentUserId();

        if (! $actorPersonId) {
            abort(401, 'Unauthenticated.');
        }

        $message = $this->chatService->deleteMessage($messageId, $actorPersonId);

        event(new ChatMessageDeleted($message));

        return $this->jsonResponse(true);
    }

    private function mapMessage(ChatMessage $message): array
    {
        return [
            'Id' => $message->Id,
            'ConversationId' => $message->ConversationId,
            'SenderPersonId' => $message->SenderPersonId,
            'MessageType' => $message->MessageType,
            'Body' => $message->Body,
            'Metadata' => $message->Metadata,
            'ReplyToMessageId' => $message->ReplyToMessageId,
            'EditedAt' => $message->EditedAt,
            'CreatedAt' => $message->CreatedAt,
            'UpdatedAt' => $message->UpdatedAt,
            'Sender' => $message->sender ? [
                'Id' => $message->sender->Id,
                'Name' => $message->sender->Name,
                'AvatarUrl' => $message->sender->AvatarUrl,
            ] : null,
            'ReplyTo' => $message->replyTo ? [
                'Id' => $message->replyTo->Id,
                'ConversationId' => $message->replyTo->ConversationId,
                'SenderPersonId' => $message->replyTo->SenderPersonId,
                'Body' => $message->replyTo->Body,
                'CreatedAt' => $message->replyTo->CreatedAt,
                'Sender' => $message->replyTo->sender ? [
                    'Id' => $message->replyTo->sender->Id,
                    'Name' => $message->replyTo->sender->Name,
                    'AvatarUrl' => $message->replyTo->sender->AvatarUrl,
                ] : null,
            ] : null,
        ];
    }
}
