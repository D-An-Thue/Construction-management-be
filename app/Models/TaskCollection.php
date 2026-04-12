<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCollection extends Model
{
    use HasFactory;
    use HasAuditColumns;

    protected $table = 'TaskCollections';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'TaskTitle',
        'TaskDescription',
        'GroupId',
        'AssignToUserId',
        'Status',
        'Priority',
        'ReferenceGroupUserID',
        'AttachLink',
        'TicketReferenceIds',
        'Cost',
        'DueDate',
        'TransactionId',
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
            'GroupId' => 'integer',
            'AssignToUserId' => 'integer',
            'Status' => 'integer',
            'Priority' => 'integer',
            'ReferenceGroupUserID' => 'array',
            'AttachLink' => 'array',
            'TicketReferenceIds' => 'array',
            'Cost' => 'float',
            'DueDate' => 'datetime',
            'TransactionId' => 'string',
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

    public function assignToUser()
    {
        return $this->belongsTo(PersonGroup::class, 'AssignToUserId', 'Id');
    }

    public function subTasks()
    {
        return $this->hasMany(SubTask::class, 'TaskId', 'Id');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class, 'TaskId', 'Id');
    }
}
