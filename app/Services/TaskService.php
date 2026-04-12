<?php

namespace App\Services;

use App\Models\TaskCollection;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    public function listByGroupId(int $groupId): Collection
    {
        return TaskCollection::query()
            ->notDeleted()
            ->where('GroupId', $groupId)
            ->with(['assignToUser.person'])
            ->orderByDesc('Id')
            ->get();
    }

    public function detail(int $id): TaskCollection
    {
        return TaskCollection::query()
            ->notDeleted()
            ->with([
                'assignToUser.person',
                'group',
                'subTasks' => fn ($query) => $query
                    ->where('IsDeleted', false)
                    ->with('assignToUser.person'),
            ])
            ->findOrFail($id);
    }

    public function create(array $attributes, int $actorGroupId): bool
    {
        TaskCollection::query()->create([
            'TaskTitle' => $attributes['TaskTitle'],
            'TaskDescription' => $attributes['TaskDescription'],
            'GroupId' => (int) $attributes['GroupId'],
            'AssignToUserId' => $attributes['AssignToUserId'] ?? null,
            'Status' => 0,
            'Priority' => (int) ($attributes['Priority'] ?? 1),
            'ReferenceGroupUserID' => $attributes['ReferenceGroupUserID'] ?? [],
            'AttachLink' => $attributes['AttachLink'] ?? [],
            'TicketReferenceIds' => $attributes['TicketReferenceIds'] ?? [],
            'Cost' => $attributes['Cost'] ?? 0,
            'DueDate' => $attributes['DueDate'] ?? null,
            'TransactionId' => (string) \Illuminate\Support\Str::uuid(),
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
        $task = TaskCollection::query()->notDeleted()->findOrFail((int) $attributes['Id']);

        $task->fill([
            'TaskTitle' => $attributes['TaskTitle'],
            'TaskDescription' => $attributes['TaskDescription'],
            'GroupId' => (int) $attributes['GroupId'],
            'AssignToUserId' => $attributes['AssignToUserId'] ?? null,
            'Status' => (int) ($attributes['Status'] ?? 0),
            'Priority' => (int) ($attributes['Priority'] ?? 1),
            'ReferenceGroupUserID' => $attributes['ReferenceGroupUserID'] ?? [],
            'AttachLink' => $attributes['AttachLink'] ?? [],
            'TicketReferenceIds' => $attributes['TicketReferenceIds'] ?? [],
            'Cost' => $attributes['Cost'] ?? 0,
            'DueDate' => $attributes['DueDate'] ?? null,
            'UpdatedBy' => $actorGroupId,
            'UpdatedAt' => now(),
        ]);

        $task->save();
    }

    public function delete(int $id, int $actorGroupId): void
    {
        $task = TaskCollection::query()->findOrFail($id);

        $task->fill([
            'IsDeleted' => true,
            'DeleteBy' => $actorGroupId,
            'DeleteAt' => now(),
            'UpdatedBy' => $actorGroupId,
            'UpdatedAt' => now(),
        ]);

        $task->save();
    }
}
