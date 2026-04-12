<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTask extends Model
{
    use HasFactory;
    use HasAuditColumns;

    protected $table = 'SubTasks';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'TaskId',
        'Title',
        'Description',
        'Type',
        'Status',
        'Priority',
        'AssignToUserId',
        'DueDate',
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
            'Type' => 'integer',
            'Status' => 'integer',
            'Priority' => 'integer',
            'AssignToUserId' => 'integer',
            'DueDate' => 'datetime',
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

    public function assignToUser()
    {
        return $this->belongsTo(PersonGroup::class, 'AssignToUserId', 'Id');
    }
}
