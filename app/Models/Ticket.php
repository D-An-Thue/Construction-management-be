<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    use HasAuditColumns;

    protected $table = 'Tickets';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'GroupId',
        'Title',
        'Description',
        'ApproveForUserId',
        'AssignToUserID',
        'Status',
        'Priority',
        'TicketType',
        'Amount',
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
            'ApproveForUserId' => 'integer',
            'AssignToUserID' => 'integer',
            'Status' => 'integer',
            'Priority' => 'integer',
            'TicketType' => 'integer',
            'Amount' => 'float',
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

    public function approveForUser()
    {
        return $this->belongsTo(PersonGroup::class, 'ApproveForUserId', 'Id');
    }

    public function assignToUser()
    {
        return $this->belongsTo(PersonGroup::class, 'AssignToUserID', 'Id');
    }

    public function createdByNavigation()
    {
        return $this->belongsTo(PersonGroup::class, 'CreatedBy', 'Id');
    }

    public function updatedByNavigation()
    {
        return $this->belongsTo(PersonGroup::class, 'UpdatedBy', 'Id');
    }
}
