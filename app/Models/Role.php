<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    use HasAuditColumns;

    protected $table = 'Roles';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'RoleName',
        'Description',
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
            'IsDeleted' => 'boolean',
            'CreatedBy' => 'integer',
            'UpdatedBy' => 'integer',
            'DeleteBy' => 'integer',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
            'DeleteAt' => 'datetime',
        ];
    }

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'RoleId', 'Id');
    }

    public function userRoles()
    {
        return $this->hasMany(UserRole::class, 'RoleId', 'Id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'RolePermissions', 'RoleId', 'PermissionId', 'Id', 'Id');
    }
}
