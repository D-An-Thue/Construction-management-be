<?php

namespace App\Http\Controllers\Api;

use App\Services\GroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends BaseApiController
{
    public function __construct(private readonly GroupService $groupService)
    {
    }

    public function index(): JsonResponse
    {
        $userId = $this->currentUserId() ?? 0;
        $groups = $this->groupService->listByUser($userId);

        $payload = $groups->map(function ($group) {
            return [
                'Id' => $group->Id,
                'CreatedAt' => $group->CreatedAt,
                'UpdatedAt' => $group->UpdatedAt,
                'IsDeleted' => $group->IsDeleted,
                'GroupName' => $group->GroupName,
                'Description' => $group->Description,
                'Amount' => $group->Amount,
                'MinimumAmount' => $group->MinimumAmount,
                'MaximumAmount' => $group->MaximumAmount,
                'CreatedBy' => $group->CreatedBy,
                'PersonCreate' => null,
                'UpdatedBy' => $group->UpdatedBy,
                'PersonUpdate' => null,
                'GroupStatus' => $group->GroupStatus,
            ];
        })->values();

        return response()->json($payload);
    }

    public function show(int $idGroups): JsonResponse
    {
        $group = $this->groupService->detail($idGroups);

        return response()->json([
            'Id' => $group->Id,
            'CreatedAt' => $group->CreatedAt,
            'UpdatedAt' => $group->UpdatedAt,
            'IsDeleted' => $group->IsDeleted,
            'GroupName' => $group->GroupName,
            'Description' => $group->Description,
            'Amount' => $group->Amount,
            'MinimumAmount' => $group->MinimumAmount,
            'MaximumAmount' => $group->MaximumAmount,
            'CreatedBy' => $group->CreatedBy,
            'PersonCreate' => null,
            'UpdatedBy' => $group->UpdatedBy,
            'PersonUpdate' => null,
            'GroupStatus' => $group->GroupStatus,
            'personGroups' => $this->groupService->memberPeople($idGroups),
            'Ticket' => [],
            'TaskCollections' => [],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'GroupName' => ['required', 'string'],
            'Description' => ['nullable', 'string'],
            'Amount' => ['nullable', 'integer'],
            'MinimumAmount' => ['nullable', 'integer'],
            'MaximumAmount' => ['nullable', 'integer'],
            'GroupStatus' => ['nullable', 'integer'],
        ]);

        $this->groupService->create($validated, $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'GroupName' => ['required', 'string'],
            'Description' => ['nullable', 'string'],
            'Amount' => ['nullable', 'integer'],
            'MinimumAmount' => ['nullable', 'integer'],
            'MaximumAmount' => ['nullable', 'integer'],
            'GroupStatus' => ['nullable', 'integer'],
        ]);

        $this->groupService->update($validated, $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'GroupId' => ['required', 'integer'],
        ]);

        $this->groupService->delete((int) $validated['GroupId'], $this->currentUserId() ?? 0);

        return response()->json(true);
    }
}
