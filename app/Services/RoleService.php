<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Person;
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

    public function seedPermissionsAndRoles(?int $actorId = null): array
    {
        return DB::transaction(function () use ($actorId): array {
            $now = now();
            $seedActorId = $actorId ?? 0;

            $permissions = [
                ['PermissionCode' => 'group.view', 'PermissionName' => 'Xem nhóm', 'Module' => 'Group'],
                ['PermissionCode' => 'group.create', 'PermissionName' => 'Tạo nhóm', 'Module' => 'Group'],
                ['PermissionCode' => 'group.update', 'PermissionName' => 'Sửa nhóm', 'Module' => 'Group'],
                ['PermissionCode' => 'group.delete', 'PermissionName' => 'Xóa nhóm', 'Module' => 'Group'],
                ['PermissionCode' => 'task.view', 'PermissionName' => 'Xem công việc', 'Module' => 'Task'],
                ['PermissionCode' => 'task.create', 'PermissionName' => 'Tạo công việc', 'Module' => 'Task'],
                ['PermissionCode' => 'task.update', 'PermissionName' => 'Sửa công việc', 'Module' => 'Task'],
                ['PermissionCode' => 'task.delete', 'PermissionName' => 'Xóa công việc', 'Module' => 'Task'],
                ['PermissionCode' => 'person.view', 'PermissionName' => 'Xem người dùng', 'Module' => 'Person'],
                ['PermissionCode' => 'person.update', 'PermissionName' => 'Sửa người dùng', 'Module' => 'Person'],
                ['PermissionCode' => 'role.manage', 'PermissionName' => 'Quản lý phân quyền', 'Module' => 'Role'],
                ['PermissionCode' => 'product.view', 'PermissionName' => 'Xem sản phẩm', 'Module' => 'Product'],
                ['PermissionCode' => 'product.create', 'PermissionName' => 'Tạo sản phẩm', 'Module' => 'Product'],
                ['PermissionCode' => 'product.update', 'PermissionName' => 'Sửa sản phẩm', 'Module' => 'Product'],
                ['PermissionCode' => 'product.delete', 'PermissionName' => 'Xóa sản phẩm', 'Module' => 'Product'],
            ];

            $existingPermissionCodes = Permission::query()
                ->pluck('PermissionCode')
                ->all();
            $existingPermissionCodeLookup = array_fill_keys($existingPermissionCodes, true);

            $permissionsToInsert = [];
            foreach ($permissions as $permission) {
                if (isset($existingPermissionCodeLookup[$permission['PermissionCode']])) {
                    continue;
                }

                $permissionsToInsert[] = [
                    ...$permission,
                    'IsDeleted' => false,
                    'CreatedBy' => $seedActorId,
                    'UpdatedBy' => null,
                    'DeleteBy' => null,
                    'CreatedAt' => $now,
                    'UpdatedAt' => null,
                    'DeleteAt' => null,
                ];
            }

            if ($permissionsToInsert !== []) {
                Permission::query()->insert($permissionsToInsert);
            }

            $adminRole = Role::query()->firstOrCreate(
                ['RoleName' => 'Admin'],
                [
                    'Description' => 'Quản trị viên hệ thống - có toàn quyền',
                    'IsDeleted' => false,
                    'CreatedBy' => $seedActorId,
                    'UpdatedBy' => null,
                    'DeleteBy' => null,
                    'CreatedAt' => $now,
                    'UpdatedAt' => null,
                    'DeleteAt' => null,
                ]
            );

            $memberRole = Role::query()->firstOrCreate(
                ['RoleName' => 'Member'],
                [
                    'Description' => 'Thành viên - quyền xem cơ bản',
                    'IsDeleted' => false,
                    'CreatedBy' => $seedActorId,
                    'UpdatedBy' => null,
                    'DeleteBy' => null,
                    'CreatedAt' => $now,
                    'UpdatedAt' => null,
                    'DeleteAt' => null,
                ]
            );

            if ($adminRole->IsDeleted) {
                $adminRole->fill([
                    'IsDeleted' => false,
                    'DeleteBy' => null,
                    'DeleteAt' => null,
                    'UpdatedBy' => $seedActorId,
                    'UpdatedAt' => $now,
                ])->save();
            }

            if ($memberRole->IsDeleted) {
                $memberRole->fill([
                    'IsDeleted' => false,
                    'DeleteBy' => null,
                    'DeleteAt' => null,
                    'UpdatedBy' => $seedActorId,
                    'UpdatedAt' => $now,
                ])->save();
            }

            $activePermissionRows = Permission::query()
                ->where('IsDeleted', false)
                ->get(['Id', 'PermissionCode']);

            $adminPermissionIds = $activePermissionRows->pluck('Id')->all();
            $memberPermissionIds = $activePermissionRows
                ->filter(fn ($permission) => str_ends_with((string) $permission->PermissionCode, '.view'))
                ->pluck('Id')
                ->all();

            $adminAssignedIds = RolePermission::query()
                ->where('RoleId', $adminRole->Id)
                ->pluck('PermissionId')
                ->all();
            $adminAssignedLookup = array_fill_keys($adminAssignedIds, true);

            $memberAssignedIds = RolePermission::query()
                ->where('RoleId', $memberRole->Id)
                ->pluck('PermissionId')
                ->all();
            $memberAssignedLookup = array_fill_keys($memberAssignedIds, true);

            $rolePermissionsToInsert = [];
            foreach ($adminPermissionIds as $permissionId) {
                if (! isset($adminAssignedLookup[$permissionId])) {
                    $rolePermissionsToInsert[] = [
                        'RoleId' => $adminRole->Id,
                        'PermissionId' => $permissionId,
                    ];
                }
            }
            foreach ($memberPermissionIds as $permissionId) {
                if (! isset($memberAssignedLookup[$permissionId])) {
                    $rolePermissionsToInsert[] = [
                        'RoleId' => $memberRole->Id,
                        'PermissionId' => $permissionId,
                    ];
                }
            }

            if ($rolePermissionsToInsert !== []) {
                DB::table('RolePermissions')->insert($rolePermissionsToInsert);
            }

            $adminPerson = Person::query()->where('Email', 'admin@gmail.com')->first();
            if ($adminPerson) {
                DB::table('UserRoles')->insertOrIgnore([
                    'PersonId' => $adminPerson->Id,
                    'RoleId' => $adminRole->Id,
                ]);
            }

            return [
                'Message' => 'Seed dữ liệu phân quyền thành công.',
                'SeededPermissions' => count($permissionsToInsert),
                'SeededRolePermissions' => count($rolePermissionsToInsert),
                'AdminRoleId' => $adminRole->Id,
                'MemberRoleId' => $memberRole->Id,
            ];
        });
    }
}
