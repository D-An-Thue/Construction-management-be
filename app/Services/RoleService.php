<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\UserRole;
use Illuminate\Support\Facades\DB;

class RoleService
{
    public function listRoles()
    {
        return Role::query()
            ->notDeleted()
            ->with(['permissions' => fn ($query) => $query->notDeleted()])
            ->orderBy('RoleName')
            ->get();
    }

    public function findRoleOrFail(int $roleId)
    {
        return Role::query()
            ->notDeleted()
            ->with([
                'permissions' => fn ($query) => $query->notDeleted(),
                'userRoles.person' => fn ($query) => $query->notDeleted(),
            ])
            ->findOrFail($roleId);
    }

    public function listPermissions()
    {
        return Permission::query()
            ->notDeleted()
            ->orderBy('Module')
            ->orderBy('PermissionCode')
            ->get();
    }

    public function createRole(array $attributes): Role
    {
        return Role::query()->create([
            'RoleName' => $attributes['RoleName'],
            'Description' => $attributes['Description'] ?? '',
            'IsDeleted' => false,
            'CreatedBy' => (int) ($attributes['actorId'] ?? 0),
            'UpdatedBy' => 0,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
            'DeleteAt' => null,
        ]);
    }

    public function updateRole(int $roleId, array $attributes): Role
    {
        $role = Role::query()->notDeleted()->findOrFail($roleId);

        $role->fill([
            'RoleName' => $attributes['RoleName'],
            'Description' => $attributes['Description'] ?? '',
            'UpdatedBy' => (int) ($attributes['actorId'] ?? 0),
            'UpdatedAt' => now(),
        ]);

        $role->save();

        return $role;
    }

    public function deleteRole(int $roleId, int $actorId): void
    {
        $role = Role::query()->notDeleted()->findOrFail($roleId);

        $role->fill([
            'IsDeleted' => true,
            'DeleteBy' => $actorId,
            'DeleteAt' => now(),
            'UpdatedBy' => $actorId,
            'UpdatedAt' => now(),
        ]);

        $role->save();
    }

    public function assignPermission(int $roleId, int $permissionId): void
    {
        $exists = RolePermission::query()
            ->where('RoleId', $roleId)
            ->where('PermissionId', $permissionId)
            ->exists();

        if ($exists) {
            throw new \RuntimeException('Quyền đã được gán cho vai trò này.');
        }

        RolePermission::query()->create([
            'RoleId' => $roleId,
            'PermissionId' => $permissionId,
        ]);
    }

    public function removePermission(int $roleId, int $permissionId): void
    {
        $deleted = RolePermission::query()
            ->where('RoleId', $roleId)
            ->where('PermissionId', $permissionId)
            ->delete();

        if ($deleted === 0) {
            throw new \RuntimeException('Không tìm thấy quyền trong vai trò.');
        }
    }

    public function assignUser(int $roleId, int $personId): void
    {
        $exists = UserRole::query()
            ->where('RoleId', $roleId)
            ->where('PersonId', $personId)
            ->exists();

        if ($exists) {
            throw new \RuntimeException('Người dùng đã được gán vai trò này.');
        }

        UserRole::query()->create([
            'RoleId' => $roleId,
            'PersonId' => $personId,
        ]);
    }

    public function removeUser(int $roleId, int $personId): void
    {
        $deleted = UserRole::query()
            ->where('RoleId', $roleId)
            ->where('PersonId', $personId)
            ->delete();

        if ($deleted === 0) {
            throw new \RuntimeException('Không tìm thấy người dùng trong vai trò.');
        }
    }

    /**
     * @return array{roles: array<int, string>, permissions: array<int, string>}
     */
    public function getRolesAndPermissionsByUserId(int $personId): array
    {
        $roles = DB::table('UserRoles as ur')
            ->join('Roles as r', 'r.Id', '=', 'ur.RoleId')
            ->where('ur.PersonId', $personId)
            ->where('r.IsDeleted', false)
            ->select('r.RoleName')
            ->distinct()
            ->pluck('RoleName')
            ->values()
            ->all();

        $permissions = DB::table('UserRoles as ur')
            ->join('Roles as r', 'r.Id', '=', 'ur.RoleId')
            ->join('RolePermissions as rp', 'rp.RoleId', '=', 'r.Id')
            ->join('Permissions as p', 'p.Id', '=', 'rp.PermissionId')
            ->where('ur.PersonId', $personId)
            ->where('r.IsDeleted', false)
            ->where('p.IsDeleted', false)
            ->select('p.PermissionCode')
            ->distinct()
            ->pluck('PermissionCode')
            ->values()
            ->all();

        return [
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }
}
