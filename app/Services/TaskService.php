<?php

namespace App\Services;

use App\Models\TaskCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TaskService
{
    private const STATUS_REOPEN = 4;

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
        $payload = [
            'TaskTitle' => $attributes['TaskTitle'],
            'TaskDescription' => $attributes['TaskDescription'],
            'GroupId' => (int) $attributes['GroupId'],
            'AssignToUserId' => $attributes['AssignToUserId'] ?? null,
            'Status' => 0,
            'Priority' => (int) ($attributes['Priority'] ?? 1),
            'ReferenceGroupUserID' => $attributes['ReferenceGroupUserID'] ?? [],
            'AttachLink' => $attributes['AttachLink'] ?? [],
            'DueDate' => $attributes['DueDate'] ?? null,
            'IsDeleted' => false,
            'CreatedBy' => $actorGroupId,
            'UpdatedBy' => null,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
            'DeleteAt' => null,
        ];

        if (Schema::hasColumn('TaskCollections', 'TransactionId')) {
            $payload['TransactionId'] = (string) Str::uuid();
        }

        TaskCollection::query()->create($payload);

        return true;
    }

    public function update(array $attributes, int $actorGroupId): void
    {
        $task = TaskCollection::query()->notDeleted()->findOrFail((int) $attributes['Id']);

        $nextStatus = (int) ($attributes['Status'] ?? 0);

        if ((int) $task->Status !== self::STATUS_REOPEN && $nextStatus < (int) $task->Status) {
            throw new \RuntimeException(sprintf(
                'Tiến trình hiện tại %d không thể chuyển về %d',
                (int) $task->Status,
                $nextStatus
            ));
        }

        $task->fill([
            'TaskTitle' => $attributes['TaskTitle'],
            'TaskDescription' => $attributes['TaskDescription'],
            'GroupId' => (int) $attributes['GroupId'],
            'AssignToUserId' => $attributes['AssignToUserId'] ?? null,
            'Status' => $nextStatus,
            'Priority' => (int) ($attributes['Priority'] ?? 1),
            'ReferenceGroupUserID' => $attributes['ReferenceGroupUserID'] ?? [],
            'AttachLink' => $attributes['AttachLink'] ?? [],
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
