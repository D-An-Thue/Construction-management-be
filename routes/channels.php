<?php

use App\Models\Person;
use App\Services\ChatService;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.conversation.{conversationId}', function (Person $person, int $conversationId) {
    if (! app(ChatService::class)->canAccessConversation($person->Id, $conversationId)) {
        return false;
    }

    return [
        'id' => $person->Id,
        'name' => $person->Name,
        'avatarUrl' => $person->AvatarUrl,
    ];
});

Broadcast::channel('chat.user.{personId}', function (Person $person, int $personId) {
    return (int) $person->Id === $personId;
});
