<?php

namespace App\Http\Controllers\Api;

use App\Models\SubTask;
use App\Services\SubTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubTaskController extends BaseApiController
{
    public function __construct(private readonly SubTaskService $subTaskService)
    {
    }

    public function index(int $taskId): JsonResponse
    {
        $subTasks = $this->subTaskService->listByTaskId($taskId)
            ->map(fn ($subTask) => $this->mapSubTask($subTask))
            ->values();

        return response()->json($subTasks);
    }

    public function store(int $taskId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Title' => ['required', 'string'],
            'Description' => ['nullable', 'string'],
            'Type' => ['required', 'integer'],
            'Status' => ['nullable', 'integer'],
            'Priority' => ['nullable', 'integer'],
            'AssignToUserId' => ['nullable', 'integer'],
            'DueDate' => ['nullable', 'date'],
        ]);

        $validated['TaskId'] = $taskId;

        $this->subTaskService->create($validated, $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function update(int $taskId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Id' => ['required', 'integer'],
            'Title' => ['required', 'string'],
            'Description' => ['nullable', 'string'],
            'Type' => ['required', 'integer'],
            'Status' => ['nullable', 'integer'],
            'Priority' => ['nullable', 'integer'],
            'AssignToUserId' => ['nullable', 'integer'],
            'DueDate' => ['nullable', 'date'],
        ]);

        $validated['TaskId'] = $taskId;

        $this->subTaskService->update($validated, $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function destroy(int $taskId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Id' => ['required', 'integer'],
        ]);

        $subTask = SubTask::query()->notDeleted()->findOrFail((int) $validated['Id']);

        if ((int) $subTask->TaskId !== $taskId) {
            abort(404, 'Subtask not found in task.');
        }

        $this->subTaskService->delete((int) $validated['Id'], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    private function mapSubTask(object $subTask): array
    {
        return [
            'Id' => $subTask->Id,
            'TaskId' => $subTask->TaskId,
            'Title' => $subTask->Title,
            'Description' => $subTask->Description,
            'Type' => $subTask->Type,
            'Status' => $subTask->Status,
            'Priority' => $subTask->Priority,
            'AssignToUserId' => $subTask->AssignToUserId,
            'DueDate' => $subTask->DueDate,
            'AssignToUser' => $subTask->assignToUser ? [
                'Id' => $subTask->assignToUser->Id,
                'PersonId' => $subTask->assignToUser->PersonId,
                'NickName' => $subTask->assignToUser->NickName,
            ] : null,
        ];
    }
}
