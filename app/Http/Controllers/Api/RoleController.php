<?php

namespace App\Http\Controllers\Api;

use App\Services\RoleService;
use App\Support\ProblemDetails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleController extends BaseApiController
{
    public function __construct(private readonly RoleService $roleService)
    {
    }

    public function index(): JsonResponse
    {
        $roles = $this->roleService->listRoles()->map(function ($role) {
            return [
                'Id' => $role->Id,
                'RoleName' => $role->RoleName,
                'Description' => $role->Description,
                'CreatedAt' => $role->CreatedAt,
                'Permissions' => $role->permissions->map(fn ($permission) => [
                    'Id' => $permission->Id,
                    'PermissionCode' => $permission->PermissionCode,
                    'PermissionName' => $permission->PermissionName,
                    'Module' => $permission->Module,
                ])->values(),
            ];
        })->values();

        return response()->json($roles);
    }

    public function show(int $roleId): JsonResponse
    {
        try {
            $role = $this->roleService->findRoleOrFail($roleId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            throw new NotFoundHttpException('Không tìm thấy vai trò.');
        }

        return response()->json([
            'Id' => $role->Id,
            'RoleName' => $role->RoleName,
            'Description' => $role->Description,
            'CreatedAt' => $role->CreatedAt,
            'Permissions' => $role->permissions->map(fn ($permission) => [
                'Id' => $permission->Id,
                'PermissionCode' => $permission->PermissionCode,
                'PermissionName' => $permission->PermissionName,
                'Module' => $permission->Module,
            ])->values(),
            'Users' => $role->userRoles
                ->filter(fn ($userRole) => $userRole->person !== null)
                ->map(fn ($userRole) => [
                    'PersonId' => $userRole->person->Id,
                    'Name' => $userRole->person->Name,
                    'Email' => $userRole->person->Email,
                ])
                ->values(),
        ]);
    }

    public function me(): JsonResponse
    {
        $userId = $this->currentUserId();

        if (! $userId) {
            return ProblemDetails::json(401, 'User not authenticated');
        }

        $me = $this->roleService->getRolesAndPermissionsByUserId($userId);

        $claims = request()->attributes->get('jwt.claims', []);
        $name = is_array($claims) ? (string) ($claims['name'] ?? '') : '';
        $email = is_array($claims) ? (string) ($claims['email'] ?? '') : '';

        return response()->json([
            'Id' => $userId,
            'Name' => $name,
            'Email' => $email,
            'AvatarUrl' => '',
            'Roles' => $me['roles'],
            'Permissions' => $me['permissions'],
        ]);
    }

    public function permissions(): JsonResponse
    {
        $permissions = $this->roleService->listPermissions()
            ->map(fn ($permission) => [
                'Id' => $permission->Id,
                'PermissionCode' => $permission->PermissionCode,
                'PermissionName' => $permission->PermissionName,
                'Module' => $permission->Module,
            ])
            ->values();

        return response()->json($permissions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'roleName' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $actorId = $this->currentUserId() ?? 0;

        $this->roleService->createRole([
            'RoleName' => $validated['roleName'],
            'Description' => $validated['description'] ?? '',
            'actorId' => $actorId,
        ]);

        return response()->json(true);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'roleName' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $actorId = $this->currentUserId() ?? 0;

        $this->roleService->updateRole((int) $validated['id'], [
            'RoleName' => $validated['roleName'],
            'Description' => $validated['description'] ?? '',
            'actorId' => $actorId,
        ]);

        return response()->json(true);
    }

    public function destroy(int $roleId): JsonResponse
    {
        $actorId = $this->currentUserId() ?? 0;
        $this->roleService->deleteRole($roleId, $actorId);

        return response()->json(true);
    }

    public function assignPermission(int $roleId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'permissionId' => ['required', 'integer'],
        ]);

        $this->roleService->assignPermission($roleId, (int) $validated['permissionId']);

        return response()->json(true);
    }

    public function removePermission(int $roleId, int $permissionId): JsonResponse
    {
        $this->roleService->removePermission($roleId, $permissionId);

        return response()->json(true);
    }

    public function assignUser(int $roleId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'personId' => ['required', 'integer'],
        ]);

        $this->roleService->assignUser($roleId, (int) $validated['personId']);

        return response()->json(true);
    }

    public function removeUser(int $roleId, int $personId): JsonResponse
    {
        $this->roleService->removeUser($roleId, $personId);

        return response()->json(true);
    }

    public function seed(): JsonResponse
    {
        return response()->json([
            'status' => 501,
            'title' => 'Not Implemented',
            'detail' => 'Role seed endpoint is not migrated yet.',
        ], 501);
    }
}
