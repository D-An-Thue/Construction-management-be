<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;
    use HasAuditColumns;

    protected $table = 'Persons';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'Name',
        'Sex',
        'Email',
        'AvatarUrl',
        'DateOfBirth',
        'PhoneNumber',
        'Address',
        'Password',
        'BankID',
        'BankAccountNumber',
        'BankName',
        'IsDeleted',
        'CreatedBy',
        'UpdatedBy',
        'DeleteBy',
        'CreatedAt',
        'UpdatedAt',
        'DeleteAt',
    ];

    protected $hidden = [
        'Password',
    ];

    protected function casts(): array
    {
        return [
            'Id' => 'integer',
            'Sex' => 'integer',
            'DateOfBirth' => 'datetime',
            'IsDeleted' => 'boolean',
            'CreatedBy' => 'integer',
            'UpdatedBy' => 'integer',
            'DeleteBy' => 'integer',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
            'DeleteAt' => 'datetime',
        ];
    }

    public function userRoles()
    {
        return $this->hasMany(UserRole::class, 'PersonId', 'Id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'UserRoles', 'PersonId', 'RoleId', 'Id', 'Id');
    }

    public function personGroups()
    {
        return $this->hasMany(PersonGroup::class, 'PersonId', 'Id');
    }
}
