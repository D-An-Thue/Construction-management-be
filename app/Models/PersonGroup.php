<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonGroup extends Model
{
    use HasFactory;
    use HasAuditColumns;

    protected $table = 'PersonGroups';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'GroupId',
        'PersonId',
        'NickName',
        'JoinDate',
        'IsAdmin',
        'JoinEnums',
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
            'PersonId' => 'integer',
            'JoinDate' => 'datetime',
            'IsAdmin' => 'boolean',
            'JoinEnums' => 'integer',
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

    public function person()
    {
        return $this->belongsTo(Person::class, 'PersonId', 'Id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'GroupId', 'Id');
    }
}
