<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;
    use HasAuditColumns;

    public const TYPE_TEXT = 1;

    public const TYPE_FILE = 2;

    public const TYPE_SYSTEM = 3;

    protected $table = 'ChatMessages';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'ConversationId',
        'SenderPersonId',
        'MessageType',
        'Body',
        'Metadata',
        'ReplyToMessageId',
        'EditedAt',
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
            'SenderPersonId' => 'integer',
            'MessageType' => 'integer',
            'Metadata' => 'array',
            'ReplyToMessageId' => 'integer',
            'EditedAt' => 'datetime',
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

    public function sender()
    {
        return $this->belongsTo(Person::class, 'SenderPersonId', 'Id');
    }

    public function replyTo()
    {
        return $this->belongsTo(ChatMessage::class, 'ReplyToMessageId', 'Id');
    }
}
