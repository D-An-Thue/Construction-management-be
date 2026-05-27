<?php

namespace App\Http\Controllers\Api;

use App\Events\ChatConversationRead;
use App\Events\ChatTyping;
use App\Models\ChatConversation;
use App\Models\Person;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatConversationController extends BaseApiController
{
    public function __construct(private readonly ChatService $chatService)
    {
    }

    public function index(): JsonResponse
    {
        $actorPersonId = $this->currentUserId();

        if (! $actorPersonId) {
            abort(401, 'Unauthenticated.');
        }

        $conversations = $this->chatService->listConversations($actorPersonId)
            ->map(fn (ChatConversation $conversation) => $this->mapConversationSummary($conversation, $actorPersonId))
            ->values();

        return $this->jsonResponse($conversations);
    }

    public function createGroup(Request $request): JsonResponse
    {
        $actorPersonId = $this->currentUserId();

        if (! $actorPersonId) {
            abort(401, 'Unauthenticated.');
        }

        $validated = $request->validate([
            'groupId' => ['required', 'integer'],
        ]);

        $conversation = $this->chatService->getOrCreateGroupConversation((int) $validated['groupId'], $actorPersonId);

        return $this->jsonResponse($this->mapConversationSummary($conversation, $actorPersonId));
    }

    public function createDirect(Request $request): JsonResponse
    {
        $actorPersonId = $this->currentUserId();

        if (! $actorPersonId) {
            abort(401, 'Unauthenticated.');
        }

        $validated = $request->validate([
            'recipientPersonId' => ['required', 'integer'],
        ]);

        $conversation = $this->chatService->getOrCreateDirectConversation((int) $validated['recipientPersonId'], $actorPersonId);

        return $this->jsonResponse($this->mapConversationSummary($conversation, $actorPersonId));
    }

    public function markRead(int $conversationId, Request $request): JsonResponse
    {
        $actorPersonId = $this->currentUserId();

        if (! $actorPersonId) {
            abort(401, 'Unauthenticated.');
        }

        $validated = $request->validate([
            'messageId' => ['required', 'integer'],
        ]);

        $participant = $this->chatService->markRead($conversationId, $actorPersonId, (int) $validated['messageId']);
        $person = $this->resolveCurrentPerson($request, $actorPersonId);

        event(new ChatConversationRead(
            $conversationId,
            $person,
            (int) $participant->LastReadMessageId,
            $participant->LastReadAt
        ));

        return $this->jsonResponse([
            'ConversationId' => $conversationId,
            'MessageId' => $participant->LastReadMessageId,
            'ReadAt' => $participant->LastReadAt,
        ]);
    }

    public function typing(int $conversationId, Request $request): JsonResponse
    {
        $actorPersonId = $this->currentUserId();

        if (! $actorPersonId) {
            abort(401, 'Unauthenticated.');
        }

        $validated = $request->validate([
            'isTyping' => ['nullable', 'boolean'],
        ]);

        if (! $this->chatService->canAccessConversation($actorPersonId, $conversationId)) {
            abort(403, 'Forbidden.');
        }

        $isTyping = (bool) ($validated['isTyping'] ?? true);
        $person = $this->resolveCurrentPerson($request, $actorPersonId);

        event(new ChatTyping($conversationId, $person, $isTyping));

        return $this->jsonResponse([
            'ConversationId' => $conversationId,
            'PersonId' => $actorPersonId,
            'IsTyping' => $isTyping,
        ]);
    }

    private function resolveCurrentPerson(Request $request, int $actorPersonId): Person
    {
        $person = $request->user();

        if ($person instanceof Person) {
            return $person;
        }

        return Person::query()->notDeleted()->findOrFail($actorPersonId);
    }

    private function mapConversationSummary(ChatConversation $conversation, int $actorPersonId): array
    {
        $otherParticipant = $conversation->participants
            ->first(fn ($participant) => (int) $participant->PersonId !== $actorPersonId);

        $title = (int) $conversation->Type === ChatConversation::TYPE_GROUP
            ? ($conversation->group?->GroupName ?? '')
            : ($otherParticipant?->person?->Name ?? '');

        $avatarUrl = (int) $conversation->Type === ChatConversation::TYPE_GROUP
            ? null
            : ($otherParticipant?->person?->AvatarUrl ?? null);

        $participant = $conversation->participants
            ->first(fn ($item) => (int) $item->PersonId === $actorPersonId);

        $unreadCount = $this->countUnreadMessages(
            $conversation->Id,
            $actorPersonId,
            $participant?->LastReadMessageId
        );

        return [
            'Id' => $conversation->Id,
            'Type' => $conversation->Type,
            'GroupId' => $conversation->GroupId,
            'Title' => $title,
            'AvatarUrl' => $avatarUrl,
            'LastMessage' => $conversation->lastMessage ? [
                'Id' => $conversation->lastMessage->Id,
                'Body' => $conversation->lastMessage->Body,
                'SenderPersonId' => $conversation->lastMessage->SenderPersonId,
                'CreatedAt' => $conversation->lastMessage->CreatedAt,
            ] : null,
            'LastMessageAt' => $conversation->LastMessageAt,
            'UnreadCount' => $unreadCount,
        ];
    }

    private function countUnreadMessages(int $conversationId, int $actorPersonId, ?int $lastReadMessageId): int
    {
        return \App\Models\ChatMessage::query()
            ->notDeleted()
            ->where('ConversationId', $conversationId)
            ->where('SenderPersonId', '!=', $actorPersonId)
            ->when($lastReadMessageId !== null, fn ($query) => $query->where('Id', '>', $lastReadMessageId))
            ->count();
    }
}
