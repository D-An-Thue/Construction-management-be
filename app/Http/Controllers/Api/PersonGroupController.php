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
            'GroupId' => ['required', 'integer'],
            'PersonId' => ['required', 'integer'],
            'NickName' => ['nullable', 'string'],
            'JoinDate' => ['nullable', 'date'],
            'IsAdmin' => ['nullable', 'boolean'],
            'JoinEnums' => ['nullable', 'integer'],
        ]);

        $this->personGroupService->create($validated, $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Id' => ['required', 'integer'],
            'NickName' => ['nullable', 'string'],
            'JoinEnums' => ['nullable', 'integer'],
        ]);

        $this->personGroupService->update($validated, $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Id' => ['required', 'integer'],
        ]);

        $this->personGroupService->delete((int) $validated['Id'], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function setAdmin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Id' => ['required', 'integer'],
            'IsAdmin' => ['required', 'boolean'],
        ]);

        $this->personGroupService->setAdmin((int) $validated['Id'], (bool) $validated['IsAdmin'], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function setStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Id' => ['required', 'integer'],
            'JoinEnums' => ['required', 'integer'],
        ]);

        $this->personGroupService->setStatus((int) $validated['Id'], (int) $validated['JoinEnums'], $this->currentUserId() ?? 0);

        return response()->json(true);
    }
}
