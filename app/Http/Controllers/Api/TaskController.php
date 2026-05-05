<?php

namespace App\Http\Controllers\Api;

use App\Services\TaskCommentService;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends BaseApiController
{
    public function __construct(
        private readonly TaskService $taskService,
        private readonly TaskCommentService $taskCommentService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'groupId' => ['required', 'integer'],
        ]);

        $tasks = $this->taskService->listByGroupId((int) $validated['groupId'])
            ->map(fn ($task) => $this->mapTask($task))
            ->values();

        return $this->jsonResponse($tasks);
    }

    public function show(int $id): JsonResponse
    {
        $task = $this->taskService->detail($id);

        $payload = $this->mapTask($task, true);
        $payload['Comments'] = $this->taskCommentService->listByTaskId($id)
            ->map(fn ($comment) => [
                'Id' => $comment->Id,
                'TaskId' => $comment->TaskId,
                'CommentByUserId' => $comment->CommentByUserId,
                'Content' => $comment->Content,
                'ParentCommentId' => $comment->ParentCommentId,
                'CreatedAt' => $comment->CreatedAt,
                'CommentByUser' => $comment->commentByUser ? [
                    'Id' => $comment->commentByUser->Id,
                    'PersonId' => $comment->commentByUser->PersonId,
                    'NickName' => $comment->commentByUser->NickName,
                    'Person' => $comment->commentByUser->person ? [
                        'Id' => $comment->commentByUser->person->Id,
                        'Name' => $comment->commentByUser->person->Name,
                        'Email' => $comment->commentByUser->person->Email,
                        'AvatarUrl' => $comment->commentByUser->person->AvatarUrl,
                    ] : null,
                ] : null,
                'Replies' => $comment->replies
                    ->map(fn ($reply) => [
                        'Id' => $reply->Id,
                        'TaskId' => $reply->TaskId,
                        'CommentByUserId' => $reply->CommentByUserId,
                        'Content' => $reply->Content,
                        'ParentCommentId' => $reply->ParentCommentId,
                        'CreatedAt' => $reply->CreatedAt,
                    ])
                    ->values(),
            ])
            ->values();

        return $this->jsonResponse($payload);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'taskTitle' => ['required', 'string'],
            'taskDescription' => ['nullable', 'string'],
            'groupId' => ['required', 'integer'],
            'assignToUserId' => ['nullable', 'integer'],
            'priority' => ['nullable', 'integer'],
            'referenceGroupUserId' => ['nullable', 'array'],
            'attachLink' => ['nullable', 'array'],
            'dueDate' => ['nullable', 'date'],
        ]);

        $this->taskService->create([
            'TaskTitle' => $validated['taskTitle'],
            'TaskDescription' => $validated['taskDescription'] ?? null,
            'GroupId' => $validated['groupId'],
            'AssignToUserId' => $validated['assignToUserId'] ?? null,
            'Priority' => $validated['priority'] ?? null,
            'ReferenceGroupUserID' => $validated['referenceGroupUserId'] ?? null,
            'AttachLink' => $validated['attachLink'] ?? null,
            'DueDate' => $validated['dueDate'] ?? null,
        ], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'taskTitle' => ['required', 'string'],
            'taskDescription' => ['nullable', 'string'],
            'groupId' => ['required', 'integer'],
            'assignToUserId' => ['nullable', 'integer'],
            'status' => ['nullable', 'integer'],
            'priority' => ['nullable', 'integer'],
            'referenceGroupUserId' => ['nullable', 'array'],
            'attachLink' => ['nullable', 'array'],
            'dueDate' => ['nullable', 'date'],
        ]);

        $this->taskService->update([
            'Id' => $validated['id'],
            'TaskTitle' => $validated['taskTitle'],
            'TaskDescription' => $validated['taskDescription'] ?? null,
            'GroupId' => $validated['groupId'],
            'AssignToUserId' => $validated['assignToUserId'] ?? null,
            'Status' => $validated['status'] ?? null,
            'Priority' => $validated['priority'] ?? null,
            'ReferenceGroupUserID' => $validated['referenceGroupUserId'] ?? null,
            'AttachLink' => $validated['attachLink'] ?? null,
            'DueDate' => $validated['dueDate'] ?? null,
        ], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);

        $this->taskService->delete((int) $validated['id'], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
    }

    private function mapTask(object $task, bool $withDetails = false): array
    {
        $payload = [
            'Id' => $task->Id,
            'TaskTitle' => $task->TaskTitle,
            'TaskDescription' => $task->TaskDescription,
            'GroupId' => $task->GroupId,
            'AssignToUserId' => $task->AssignToUserId,
            'Status' => $task->Status,
            'Priority' => $task->Priority,
            'ReferenceGroupUserID' => $task->ReferenceGroupUserID,
            'AttachLink' => $task->AttachLink,
            'DueDate' => $task->DueDate,
            'CreatedAt' => $task->CreatedAt,
            'UpdatedAt' => $task->UpdatedAt,
            'AssignToUser' => $task->assignToUser ? [
                'Id' => $task->assignToUser->Id,
                'GroupId' => $task->assignToUser->GroupId,
                'PersonId' => $task->assignToUser->PersonId,
                'NickName' => $task->assignToUser->NickName,
            ] : null,
        ];

        if (! $withDetails) {
            return $payload;
        }

        $payload['Group'] = $task->group ? [
            'Id' => $task->group->Id,
            'GroupName' => $task->group->GroupName,
        ] : null;

        $payload['SubTasks'] = $task->subTasks
            ->map(fn ($subTask) => [
                'Id' => $subTask->Id,
                'TaskId' => $subTask->TaskId,
                'Title' => $subTask->Title,
                'Description' => $subTask->Description,
                'Type' => $subTask->Type,
                'Status' => $subTask->Status,
                'Priority' => $subTask->Priority,
                'AssignToUserId' => $subTask->AssignToUserId,
                'DueDate' => $subTask->DueDate,
            ])
            ->values();

        return $payload;
    }
}
