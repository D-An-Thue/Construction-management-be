<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatConversationParticipant extends Model
{
    use HasFactory;
    use HasAuditColumns;

    protected $table = 'ChatConversationParticipants';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'ConversationId',
        'PersonId',
        'LastReadMessageId',
        'LastReadAt',
        'MutedUntil',
        'IsDeleted',
        'CreatedBy',
        'UpdatedBy',
        'DeleteBy',
        'CreatedAt',
        'UpdatedAt',
        'DeleteAt',
    ];

    protected function casts(): array
    {
        return [
            'Id' => 'integer',
            'ConversationId' => 'integer',
            'PersonId' => 'integer',
            'LastReadMessageId' => 'integer',
            'LastReadAt' => 'datetime',
            'MutedUntil' => 'datetime',
            'IsDeleted' => 'boolean',
            'CreatedBy' => 'integer',
            'UpdatedBy' => 'integer',
            'DeleteBy' => 'integer',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
            'DeleteAt' => 'datetime',
        ];
    }

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'ConversationId', 'Id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'PersonId', 'Id');
    }

    public function lastReadMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'LastReadMessageId', 'Id');
    }
}
