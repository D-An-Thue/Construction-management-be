<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    use HasFactory;
    use HasAuditColumns;

    public const TYPE_GROUP = 1;

    public const TYPE_DIRECT = 2;

    protected $table = 'ChatConversations';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'Type',
        'GroupId',
        'DirectKey',
        'LastMessageId',
        'LastMessageAt',
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
            'Type' => 'integer',
            'GroupId' => 'integer',
            'LastMessageId' => 'integer',
            'LastMessageAt' => 'datetime',
            'IsDeleted' => 'boolean',
            'CreatedBy' => 'integer',
            'UpdatedBy' => 'integer',
            'DeleteBy' => 'integer',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
            'DeleteAt' => 'datetime',
        ];
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'GroupId', 'Id');
    }

    public function participants()
    {
        return $this->hasMany(ChatConversationParticipant::class, 'ConversationId', 'Id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'ConversationId', 'Id');
    }

    public function lastMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'LastMessageId', 'Id');
    }
}
