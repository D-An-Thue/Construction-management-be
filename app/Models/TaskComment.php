<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    use HasFactory;
    use HasAuditColumns;

    protected $table = 'TaskComments';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'TaskId',
        'CommentByUserId',
        'Content',
        'ParentCommentId',
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
            'TaskId' => 'integer',
            'CommentByUserId' => 'integer',
            'ParentCommentId' => 'integer',
            'IsDeleted' => 'boolean',
            'CreatedBy' => 'integer',
            'UpdatedBy' => 'integer',
            'DeleteBy' => 'integer',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
            'DeleteAt' => 'datetime',
        ];
    }

    public function task()
    {
        return $this->belongsTo(TaskCollection::class, 'TaskId', 'Id');
    }

    public function commentByUser()
    {
        return $this->belongsTo(PersonGroup::class, 'CommentByUserId', 'Id');
    }

    public function replies()
    {
        return $this->hasMany(TaskComment::class, 'ParentCommentId', 'Id');
    }
}
