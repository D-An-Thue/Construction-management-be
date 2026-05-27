<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatConversationParticipant;
use App\Models\ChatMessage;
use App\Models\Group;
use App\Models\Person;
use App\Models\PersonGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ChatService
{
    public function listConversations(int $personId): Collection
    {
        $directConversationIds = ChatConversationParticipant::query()
            ->notDeleted()
            ->where('PersonId', $personId)
            ->pluck('ConversationId');

        $groupIds = PersonGroup::query()
            ->notDeleted()
            ->where('PersonId', $personId)
            ->pluck('GroupId');

        $query = ChatConversation::query()
            ->notDeleted()
            ->with([
                'group',
                'participants' => fn ($participants) => $participants
                    ->where('IsDeleted', false)
                    ->with('person'),
                'lastMessage.sender',
            ]);

        $query->where(function ($builder) use ($directConversationIds, $groupIds) {
            $hasCondition = false;

            if ($directConversationIds->isNotEmpty()) {
                $builder->where(function ($directQuery) use ($directConversationIds) {
                    $directQuery->where('Type', ChatConversation::TYPE_DIRECT)
                        ->whereIn('Id', $directConversationIds);
                });

                $hasCondition = true;
            }

            if ($groupIds->isNotEmpty()) {
                $method = $hasCondition ? 'orWhere' : 'where';

                $builder->{$method}(function ($groupQuery) use ($groupIds) {
                    $groupQuery->where('Type', ChatConversation::TYPE_GROUP)
                        ->whereIn('GroupId', $groupIds);
                });

                $hasCondition = true;
            }

            if (! $hasCondition) {
                $builder->whereRaw('1 = 0');
            }
        });

        return $query
            ->orderByDesc('LastMessageAt')
            ->orderByDesc('Id')
            ->get();
    }

    public function getOrCreateGroupConversation(int $groupId, int $actorPersonId): ChatConversation
    {
        $group = Group::query()->notDeleted()->findOrFail($groupId);

        $isMember = PersonGroup::query()
            ->notDeleted()
            ->where('GroupId', $groupId)
            ->where('PersonId', $actorPersonId)
            ->exists();

        if (! $isMember) {
            abort(403, 'Forbidden.');
        }

        return DB::transaction(function () use ($group, $actorPersonId) {
            $conversation = ChatConversation::query()
                ->where('Type', ChatConversation::TYPE_GROUP)
                ->where('GroupId', $group->Id)
                ->first();

            if (! $conversation) {
                $conversation = ChatConversation::query()->create([
                    'Type' => ChatConversation::TYPE_GROUP,
                    'GroupId' => $group->Id,
                    'DirectKey' => null,
                    'LastMessageId' => null,
                    'LastMessageAt' => null,
                    'IsDeleted' => false,
                    'CreatedBy' => $actorPersonId,
                    'UpdatedBy' => null,
                    'DeleteBy' => null,
                    'CreatedAt' => now(),
                    'UpdatedAt' => null,
                    'DeleteAt' => null,
                ]);
            } elseif ($conversation->IsDeleted) {
                $conversation->fill([
                    'IsDeleted' => false,
                    'DeleteBy' => null,
                    'DeleteAt' => null,
                    'UpdatedBy' => $actorPersonId,
                    'UpdatedAt' => now(),
                ])->save();
            }

            $this->syncGroupConversationParticipants($conversation->Id, $group->Id, $actorPersonId);

            return $this->loadConversation($conversation->Id);
        });
    }

    public function getOrCreateDirectConversation(int $recipientPersonId, int $actorPersonId): ChatConversation
    {
        if ($recipientPersonId === $actorPersonId) {
            throw new InvalidArgumentException('Khong the tao direct conversation voi chinh minh.');
        }

        Person::query()->notDeleted()->findOrFail($recipientPersonId);

        $ids = [$actorPersonId, $recipientPersonId];
        sort($ids);
        $directKey = $ids[0].':'.$ids[1];

        return DB::transaction(function () use ($actorPersonId, $recipientPersonId, $directKey) {
            $conversation = ChatConversation::query()
                ->where('DirectKey', $directKey)
                ->first();

            if (! $conversation) {
                $conversation = ChatConversation::query()->create([
                    'Type' => ChatConversation::TYPE_DIRECT,
                    'GroupId' => null,
                    'DirectKey' => $directKey,
                    'LastMessageId' => null,
                    'LastMessageAt' => null,
                    'IsDeleted' => false,
                    'CreatedBy' => $actorPersonId,
                    'UpdatedBy' => null,
                    'DeleteBy' => null,
                    'CreatedAt' => now(),
                    'UpdatedAt' => null,
                    'DeleteAt' => null,
                ]);
            } elseif ($conversation->IsDeleted) {
                $conversation->fill([
                    'IsDeleted' => false,
                    'DeleteBy' => null,
                    'DeleteAt' => null,
                    'UpdatedBy' => $actorPersonId,
                    'UpdatedAt' => now(),
                ])->save();
            }

            $this->upsertParticipant($conversation->Id, $actorPersonId, $actorPersonId);
            $this->upsertParticipant($conversation->Id, $recipientPersonId, $actorPersonId);

            return $this->loadConversation($conversation->Id);
        });
    }

    public function listMessages(int $conversationId, int $actorPersonId, ?int $beforeId, int $limit): Collection
    {
        $this->ensureConversationAccess($actorPersonId, $conversationId);

        $messages = ChatMessage::query()
            ->notDeleted()
            ->where('ConversationId', $conversationId)
            ->when($beforeId !== null, fn ($query) => $query->where('Id', '<', $beforeId))
            ->with([
                'sender',
                'replyTo.sender',
            ])
            ->orderByDesc('Id')
            ->limit(max(1, min($limit, 100)))
            ->get();

        return $messages->sortBy('Id')->values();
    }

    public function sendMessage(int $conversationId, int $actorPersonId, array $data): ChatMessage
    {
        $conversation = ChatConversation::query()->notDeleted()->findOrFail($conversationId);
        $this->ensureConversationAccess($actorPersonId, $conversationId);
        $this->ensureParticipantExists($conversation, $actorPersonId);

        $replyToMessageId = $data['ReplyToMessageId'] ?? null;

        if ($replyToMessageId !== null) {
            ChatMessage::query()
                ->notDeleted()
                ->where('ConversationId', $conversationId)
                ->findOrFail((int) $replyToMessageId);
        }

        return DB::transaction(function () use ($conversation, $actorPersonId, $data, $replyToMessageId) {
            $message = ChatMessage::query()->create([
                'ConversationId' => $conversation->Id,
                'SenderPersonId' => $actorPersonId,
                'MessageType' => (int) ($data['MessageType'] ?? ChatMessage::TYPE_TEXT),
                'Body' => $data['Body'] ?? null,
                'Metadata' => $data['Metadata'] ?? null,
                'ReplyToMessageId' => $replyToMessageId,
                'EditedAt' => null,
                'IsDeleted' => false,
                'CreatedBy' => $actorPersonId,
                'UpdatedBy' => null,
                'DeleteBy' => null,
                'CreatedAt' => now(),
                'UpdatedAt' => null,
                'DeleteAt' => null,
            ]);

            $conversation->fill([
                'LastMessageId' => $message->Id,
                'LastMessageAt' => $message->CreatedAt,
                'UpdatedBy' => $actorPersonId,
                'UpdatedAt' => now(),
            ])->save();

            return $this->loadMessage($message->Id);
        });
    }

    public function editMessage(int $messageId, int $actorPersonId, string $body): ChatMessage
    {
        $message = ChatMessage::query()->notDeleted()->findOrFail($messageId);
        $this->ensureConversationAccess($actorPersonId, (int) $message->ConversationId);

        if ((int) $message->SenderPersonId !== $actorPersonId) {
            abort(403, 'Forbidden.');
        }

        $message->fill([
            'Body' => $body,
            'EditedAt' => now(),
            'UpdatedBy' => $actorPersonId,
            'UpdatedAt' => now(),
        ]);

        $message->save();

        return $this->loadMessage($message->Id);
    }

    public function deleteMessage(int $messageId, int $actorPersonId): ChatMessage
    {
        $message = ChatMessage::query()->notDeleted()->findOrFail($messageId);
        $this->ensureConversationAccess($actorPersonId, (int) $message->ConversationId);

        if ((int) $message->SenderPersonId !== $actorPersonId) {
            abort(403, 'Forbidden.');
        }

        return DB::transaction(function () use ($message, $actorPersonId) {
            $message->fill([
                'IsDeleted' => true,
                'DeleteBy' => $actorPersonId,
                'DeleteAt' => now(),
                'UpdatedBy' => $actorPersonId,
                'UpdatedAt' => now(),
            ])->save();

            $conversation = ChatConversation::query()->findOrFail((int) $message->ConversationId);

            $latestMessage = ChatMessage::query()
                ->notDeleted()
                ->where('ConversationId', $conversation->Id)
                ->orderByDesc('Id')
                ->first();

            $conversation->fill([
                'LastMessageId' => $latestMessage?->Id,
                'LastMessageAt' => $latestMessage?->CreatedAt,
                'UpdatedBy' => $actorPersonId,
                'UpdatedAt' => now(),
            ])->save();

            return $this->loadMessage($message->Id);
        });
    }

    public function markRead(int $conversationId, int $actorPersonId, int $messageId): ChatConversationParticipant
    {
        $conversation = ChatConversation::query()->notDeleted()->findOrFail($conversationId);
        $this->ensureConversationAccess($actorPersonId, $conversationId);
        $participant = $this->ensureParticipantExists($conversation, $actorPersonId);

        ChatMessage::query()
            ->notDeleted()
            ->where('ConversationId', $conversationId)
            ->findOrFail($messageId);

        $participant->fill([
            'LastReadMessageId' => $messageId,
            'LastReadAt' => now(),
            'UpdatedBy' => $actorPersonId,
            'UpdatedAt' => now(),
        ]);

        $participant->save();

        return $participant->fresh(['lastReadMessage']);
    }

    public function canAccessConversation(int $personId, int $conversationId): bool
    {
        $conversation = ChatConversation::query()->notDeleted()->find($conversationId);

        if (! $conversation) {
            return false;
        }

        if ((int) $conversation->Type === ChatConversation::TYPE_GROUP) {
            if (! $conversation->GroupId) {
                return false;
            }

            return PersonGroup::query()
                ->notDeleted()
                ->where('GroupId', (int) $conversation->GroupId)
                ->where('PersonId', $personId)
                ->exists();
        }

        return ChatConversationParticipant::query()
            ->notDeleted()
            ->where('ConversationId', $conversationId)
            ->where('PersonId', $personId)
            ->exists();
    }

    private function ensureConversationAccess(int $personId, int $conversationId): void
    {
        if (! $this->canAccessConversation($personId, $conversationId)) {
            abort(403, 'Forbidden.');
        }
    }

    private function loadConversation(int $conversationId): ChatConversation
    {
        return ChatConversation::query()
            ->with([
                'group',
                'participants' => fn ($query) => $query
                    ->where('IsDeleted', false)
                    ->with('person'),
                'lastMessage.sender',
            ])
            ->findOrFail($conversationId);
    }

    private function loadMessage(int $messageId): ChatMessage
    {
        return ChatMessage::query()
            ->with([
                'sender',
                'replyTo.sender',
            ])
            ->findOrFail($messageId);
    }

    private function ensureParticipantExists(ChatConversation $conversation, int $personId): ChatConversationParticipant
    {
        $participant = ChatConversationParticipant::query()
            ->notDeleted()
            ->where('ConversationId', $conversation->Id)
            ->where('PersonId', $personId)
            ->first();

        if ($participant) {
            return $participant;
        }

        if ((int) $conversation->Type === ChatConversation::TYPE_GROUP && $conversation->GroupId) {
            $isMember = PersonGroup::query()
                ->notDeleted()
                ->where('GroupId', (int) $conversation->GroupId)
                ->where('PersonId', $personId)
                ->exists();

            if ($isMember) {
                return $this->upsertParticipant($conversation->Id, $personId, $personId);
            }
        }

        abort(403, 'Forbidden.');
    }

    private function syncGroupConversationParticipants(int $conversationId, int $groupId, int $actorPersonId): void
    {
        $memberIds = PersonGroup::query()
            ->notDeleted()
            ->where('GroupId', $groupId)
            ->pluck('PersonId')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $existingParticipants = ChatConversationParticipant::query()
            ->where('ConversationId', $conversationId)
            ->get()
            ->keyBy('PersonId');

        foreach ($memberIds as $personId) {
            $existing = $existingParticipants->get($personId);

            if ($existing) {
                if ($existing->IsDeleted) {
                    $existing->fill([
                        'IsDeleted' => false,
                        'DeleteBy' => null,
                        'DeleteAt' => null,
                        'UpdatedBy' => $actorPersonId,
                        'UpdatedAt' => now(),
                    ])->save();
                }

                continue;
            }

            ChatConversationParticipant::query()->create([
                'ConversationId' => $conversationId,
                'PersonId' => $personId,
                'LastReadMessageId' => null,
                'LastReadAt' => null,
                'MutedUntil' => null,
                'IsDeleted' => false,
                'CreatedBy' => $actorPersonId,
                'UpdatedBy' => null,
                'DeleteBy' => null,
                'CreatedAt' => now(),
                'UpdatedAt' => null,
                'DeleteAt' => null,
            ]);
        }

        foreach ($existingParticipants as $participant) {
            if (in_array((int) $participant->PersonId, $memberIds, true) || $participant->IsDeleted) {
                continue;
            }

            $participant->fill([
                'IsDeleted' => true,
                'DeleteBy' => $actorPersonId,
                'DeleteAt' => now(),
                'UpdatedBy' => $actorPersonId,
                'UpdatedAt' => now(),
            ])->save();
        }
    }

    private function upsertParticipant(int $conversationId, int $personId, int $actorPersonId): ChatConversationParticipant
    {
        $participant = ChatConversationParticipant::query()
            ->where('ConversationId', $conversationId)
            ->where('PersonId', $personId)
            ->first();

        if (! $participant) {
            return ChatConversationParticipant::query()->create([
                'ConversationId' => $conversationId,
                'PersonId' => $personId,
                'LastReadMessageId' => null,
                'LastReadAt' => null,
                'MutedUntil' => null,
                'IsDeleted' => false,
                'CreatedBy' => $actorPersonId,
                'UpdatedBy' => null,
                'DeleteBy' => null,
                'CreatedAt' => now(),
                'UpdatedAt' => null,
                'DeleteAt' => null,
            ]);
        }

        if ($participant->IsDeleted) {
            $participant->fill([
                'IsDeleted' => false,
                'DeleteBy' => null,
                'DeleteAt' => null,
                'UpdatedBy' => $actorPersonId,
                'UpdatedAt' => now(),
            ])->save();
        }

        return $participant;
    }
}
