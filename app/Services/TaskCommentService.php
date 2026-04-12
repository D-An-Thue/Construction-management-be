<?php

namespace App\Services;

use App\Models\PersonGroup;
use App\Models\TaskCollection;
use App\Models\TaskComment;

class TaskCommentService
{
    public function listByTaskId(int $taskId)
    {
        $comments = TaskComment::query()
            ->notDeleted()
            ->where('TaskId', $taskId)
            ->with([
                'commentByUser.person',
                'replies' => fn ($query) => $query
                    ->where('IsDeleted', false)
                    ->with('commentByUser.person'),
            ])
            ->orderByDesc('CreatedAt')
            ->get();

        return $comments->filter(fn ($comment) => $comment->ParentCommentId === null)->values();
    }

    public function create(array $attributes, int $actorUserId): bool
    {
        $task = TaskCollection::query()->notDeleted()->findOrFail((int) $attributes['TaskId']);

        $member = PersonGroup::query()
            ->notDeleted()
            ->where('GroupId', (int) $task->GroupId)
            ->where('PersonId', $actorUserId)
            ->first();

        if (! $member) {
            abort(403, 'Forbidden.');
        }

        TaskComment::query()->create([
            'TaskId' => (int) $attributes['TaskId'],
            'CommentByUserId' => $member->Id,
            'Content' => $attributes['Content'],
            'ParentCommentId' => $attributes['ParentCommentId'] ?? null,
            'IsDeleted' => false,
            'CreatedBy' => $actorUserId,
            'UpdatedBy' => null,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
            'DeleteAt' => null,
        ]);

        return true;
    }

    public function update(int $id, string $content, int $actorUserId): void
    {
        $comment = TaskComment::query()->notDeleted()->findOrFail($id);

        $task = TaskCollection::query()->notDeleted()->findOrFail((int) $comment->TaskId);

        $member = PersonGroup::query()
            ->notDeleted()
            ->where('GroupId', (int) $task->GroupId)
            ->where('PersonId', $actorUserId)
            ->first();

        if (! $member) {
            abort(403, 'Forbidden.');
        }

        $comment->fill([
            'Content' => $content,
            'UpdatedBy' => $actorUserId,
            'UpdatedAt' => now(),
        ]);

        $comment->save();
    }

    public function delete(int $id, int $actorUserId): void
    {
        $comment = TaskComment::query()->notDeleted()->findOrFail($id);

        $task = TaskCollection::query()->notDeleted()->findOrFail((int) $comment->TaskId);

        $member = PersonGroup::query()
            ->notDeleted()
            ->where('GroupId', (int) $task->GroupId)
            ->where('PersonId', $actorUserId)
            ->first();

        if (! $member) {
            abort(403, 'Forbidden.');
        }

        $comment->fill([
            'IsDeleted' => true,
            'DeleteBy' => $actorUserId,
            'DeleteAt' => now(),
            'UpdatedBy' => $actorUserId,
            'UpdatedAt' => now(),
        ]);

        $comment->save();
    }
}
