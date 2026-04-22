<?php

namespace App\Http\Controllers\Api;

use App\Services\GroupService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        return $this->jsonResponse($payload);
    }

    public function show(int $idGroups): JsonResponse
    {
        $group = $this->groupService->detail($idGroups);

        return $this->jsonResponse([
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
            'ConstructionDocuments' => $group->ConstructionDocuments,
            'personGroups' => $this->groupService->memberPeople($idGroups),
            'Ticket' => [],
            'TaskCollections' => [],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'groupName' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'amount' => ['nullable', 'integer'],
            'minimumAmount' => ['nullable', 'integer'],
            'maximumAmount' => ['nullable', 'integer'],
            'groupStatus' => ['nullable', 'integer'],
        ]);

        $this->groupService->create([
            'GroupName' => $validated['groupName'],
            'Description' => $validated['description'] ?? null,
            'Amount' => $validated['amount'] ?? null,
            'MinimumAmount' => $validated['minimumAmount'] ?? null,
            'MaximumAmount' => $validated['maximumAmount'] ?? null,
            'GroupStatus' => $validated['groupStatus'] ?? null,
        ], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'groupName' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'amount' => ['nullable', 'integer'],
            'minimumAmount' => ['nullable', 'integer'],
            'maximumAmount' => ['nullable', 'integer'],
            'groupStatus' => ['nullable', 'integer'],
        ]);

        $this->groupService->update([
            'id' => $validated['id'],
            'GroupName' => $validated['groupName'],
            'Description' => $validated['description'] ?? null,
            'Amount' => $validated['amount'] ?? null,
            'MinimumAmount' => $validated['minimumAmount'] ?? null,
            'MaximumAmount' => $validated['maximumAmount'] ?? null,
            'GroupStatus' => $validated['groupStatus'] ?? null,
        ], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'groupId' => ['required', 'integer'],
        ]);

        $this->groupService->delete((int) $validated['groupId'], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
    }

    public function updateFileUpload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'groupId' => ['required', 'integer'],
            'fileUpload' => ['required', 'array', 'min:1'],
            'fileUpload.*' => ['required', 'string', 'url', 'max:2048'],
        ]);

        try {
            $group = $this->groupService->appendConstructionDocuments(
                (int) $validated['groupId'],
                $validated['fileUpload'],
                $this->currentUserId() ?? 0
            );
        } catch (ModelNotFoundException) {
            throw new NotFoundHttpException('Không tìm thấy nhóm với groupId '.$validated['groupId'].'.');
        }

        return $this->jsonResponse([
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
            'ConstructionDocuments' => $group->ConstructionDocuments,
            'personGroups' => $this->groupService->memberPeople($group->Id),
            'Ticket' => [],
            'TaskCollections' => [],
        ]);
    }

    public function destroyFileUpload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'groupId' => ['required', 'integer'],
            'documentUrl' => ['required', 'string', 'url', 'max:2048'],
        ]);

        try {
            $group = $this->groupService->removeConstructionDocument(
                (int) $validated['groupId'],
                $validated['documentUrl'],
                $this->currentUserId() ?? 0
            );
        } catch (ModelNotFoundException) {
            throw new NotFoundHttpException('Không tìm thấy nhóm với groupId '.$validated['groupId'].'.');
        }

        return $this->jsonResponse([
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
            'ConstructionDocuments' => $group->ConstructionDocuments,
            'personGroups' => $this->groupService->memberPeople($group->Id),
            'Ticket' => [],
            'TaskCollections' => [],
        ]);
    }
}
