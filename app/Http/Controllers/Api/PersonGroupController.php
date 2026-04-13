<?php

namespace App\Http\Controllers\Api;

use App\Services\PersonGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonGroupController extends BaseApiController
{
    public function __construct(private readonly PersonGroupService $personGroupService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'groupId' => ['required', 'integer'],
            'personId' => ['required', 'integer'],
            'nickName' => ['nullable', 'string'],
            'joinDate' => ['nullable', 'date'],
            'isAdmin' => ['nullable', 'boolean'],
            'joinEnums' => ['nullable', 'integer'],
        ]);

        $this->personGroupService->create([
            'GroupId' => $validated['groupId'],
            'PersonId' => $validated['personId'],
            'NickName' => $validated['nickName'] ?? null,
            'JoinDate' => $validated['joinDate'] ?? null,
            'IsAdmin' => $validated['isAdmin'] ?? null,
            'JoinEnums' => $validated['joinEnums'] ?? null,
        ], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'nickName' => ['nullable', 'string'],
            'joinEnums' => ['nullable', 'integer'],
        ]);

        $this->personGroupService->update([
            'Id' => $validated['id'],
            'NickName' => $validated['nickName'] ?? null,
            'JoinEnums' => $validated['joinEnums'] ?? null,
        ], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);

        $this->personGroupService->delete((int) $validated['id'], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function setAdmin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'isAdmin' => ['required', 'boolean'],
        ]);

        $this->personGroupService->setAdmin((int) $validated['id'], (bool) $validated['isAdmin'], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function setStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'joinEnums' => ['required', 'integer'],
        ]);

        $this->personGroupService->setStatus((int) $validated['id'], (int) $validated['joinEnums'], $this->currentUserId() ?? 0);

        return response()->json(true);
    }
}
