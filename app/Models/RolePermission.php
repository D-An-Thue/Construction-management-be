<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RolePermission extends Pivot
{
    protected $table = 'RolePermissions';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'RoleId',
        'PermissionId',
    ];

    protected function casts(): array
    {
        return [
            'RoleId' => 'integer',
            'PermissionId' => 'integer',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'RoleId', 'Id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'PermissionId', 'Id');
    }
}
