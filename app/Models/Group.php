<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    use HasAuditColumns;

    protected $table = 'Groups';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'GroupName',
        'Description',
        'GroupStatus',
        'ConstructionDocuments',
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
            'GroupStatus' => 'integer',
            'ConstructionDocuments' => 'array',
            'IsDeleted' => 'boolean',
            'CreatedBy' => 'integer',
            'UpdatedBy' => 'integer',
            'DeleteBy' => 'integer',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
            'DeleteAt' => 'datetime',
        ];
    }

    public function members()
    {
        return $this->hasMany(PersonGroup::class, 'GroupId', 'Id');
    }

    public function creator()
    {
        return $this->belongsTo(Person::class, 'CreatedBy', 'Id');
    }

    public function updater()
    {
        return $this->belongsTo(Person::class, 'UpdatedBy', 'Id');
    }
}
