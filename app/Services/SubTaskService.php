<?php

namespace App\Services;

use App\Models\SubTask;

class SubTaskService
{
    public function listByTaskId(int $taskId)
    {
        return SubTask::query()
            ->notDeleted()
            ->where('TaskId', $taskId)
            ->with('assignToUser.person')
            ->orderByDesc('Id')
            ->get();
    }

    public function create(array $attributes, int $actorGroupId): bool
    {
        SubTask::query()->create([
            'TaskId' => (int) $attributes['TaskId'],
            'Title' => $attributes['Title'],
            'Description' => $attributes['Description'] ?? null,
            'Type' => (int) $attributes['Type'],
            'Status' => (int) ($attributes['Status'] ?? 0),
            'Priority' => (int) ($attributes['Priority'] ?? 1),
            'AssignToUserId' => $attributes['AssignToUserId'] ?? null,
            'DueDate' => $attributes['DueDate'] ?? null,
            'IsDeleted' => false,
            'CreatedBy' => $actorGroupId,
            'UpdatedBy' => null,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
            'DeleteAt' => null,
        ]);

        return true;
    }

    public function update(array $attributes, int $actorGroupId): void
    {
        $subTask = SubTask::query()->notDeleted()->findOrFail((int) $attributes['Id']);

        $subTask->fill([
            'Title' => $attributes['Title'],
            'Description' => $attributes['Description'] ?? null,
            'Type' => (int) $attributes['Type'],
            'Status' => (int) ($attributes['Status'] ?? 0),
            'Priority' => (int) ($attributes['Priority'] ?? 1),
            'AssignToUserId' => $attributes['AssignToUserId'] ?? null,
            'DueDate' => $attributes['DueDate'] ?? null,
            'UpdatedBy' => $actorGroupId,
            'UpdatedAt' => now(),
        ]);

        $subTask->save();
    }

    public function delete(int $id, int $actorGroupId): void
    {
        $subTask = SubTask::query()->findOrFail($id);

        $subTask->fill([
            'IsDeleted' => true,
            'DeleteBy' => $actorGroupId,
            'DeleteAt' => now(),
            'UpdatedBy' => $actorGroupId,
            'UpdatedAt' => now(),
        ]);

        $subTask->save();
    }
}
