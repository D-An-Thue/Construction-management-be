<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRole extends Pivot
{
    protected $table = 'UserRoles';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'PersonId',
        'RoleId',
    ];

    protected function casts(): array
    {
        return [
            'PersonId' => 'integer',
            'RoleId' => 'integer',
        ];
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'PersonId', 'Id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'RoleId', 'Id');
    }
}
