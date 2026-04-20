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

        return $this->jsonResponse($subTasks);
    }

    public function store(int $taskId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'integer'],
            'status' => ['nullable', 'integer'],
            'priority' => ['nullable', 'integer'],
            'assignToUserId' => ['nullable', 'integer'],
            'dueDate' => ['nullable', 'date'],
        ]);

        $this->subTaskService->create([
            'TaskId' => $taskId,
            'Title' => $validated['title'],
            'Description' => $validated['description'] ?? null,
            'Type' => $validated['type'],
            'Status' => $validated['status'] ?? null,
            'Priority' => $validated['priority'] ?? null,
            'AssignToUserId' => $validated['assignToUserId'] ?? null,
            'DueDate' => $validated['dueDate'] ?? null,
        ], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
    }

    public function update(int $taskId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'integer'],
            'status' => ['nullable', 'integer'],
            'priority' => ['nullable', 'integer'],
            'assignToUserId' => ['nullable', 'integer'],
            'dueDate' => ['nullable', 'date'],
        ]);

        $this->subTaskService->update([
            'Id' => $validated['id'],
            'TaskId' => $taskId,
            'Title' => $validated['title'],
            'Description' => $validated['description'] ?? null,
            'Type' => $validated['type'],
            'Status' => $validated['status'] ?? null,
            'Priority' => $validated['priority'] ?? null,
            'AssignToUserId' => $validated['assignToUserId'] ?? null,
            'DueDate' => $validated['dueDate'] ?? null,
        ], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
    }

    public function destroy(int $taskId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);

        $subTask = SubTask::query()->notDeleted()->findOrFail((int) $validated['id']);

        if ((int) $subTask->TaskId !== $taskId) {
            abort(404, 'Subtask not found in task.');
        }

        $this->subTaskService->delete((int) $validated['id'], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
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
