<?php

namespace App\Http\Controllers\Api;

use App\Services\TaskCommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskCommentController extends BaseApiController
{
    public function __construct(private readonly TaskCommentService $taskCommentService)
    {
    }

    public function index(int $taskId): JsonResponse
    {
        $comments = $this->taskCommentService->listByTaskId($taskId)
            ->map(fn ($comment) => $this->mapComment($comment))
            ->values();

        return response()->json($comments);
    }

    public function store(int $taskId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
            'parentCommentId' => ['nullable', 'integer'],
        ]);

        $actorUserId = $this->currentUserId();

        if (! $actorUserId) {
            abort(401, 'Unauthenticated.');
        }

        $this->taskCommentService->create([
            'TaskId' => $taskId,
            'Content' => $validated['content'],
            'ParentCommentId' => $validated['parentCommentId'] ?? null,
        ], $actorUserId);

        return response()->json(true);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'content' => ['required', 'string'],
        ]);

        $actorUserId = $this->currentUserId();

        if (! $actorUserId) {
            abort(401, 'Unauthenticated.');
        }

        $this->taskCommentService->update((int) $validated['id'], $validated['content'], $actorUserId);

        return response()->json(true);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);

        $actorUserId = $this->currentUserId();

        if (! $actorUserId) {
            abort(401, 'Unauthenticated.');
        }

        $this->taskCommentService->delete((int) $validated['id'], $actorUserId);

        return response()->json(true);
    }

    private function mapComment(object $comment): array
    {
        return [
            'Id' => $comment->Id,
            'TaskId' => $comment->TaskId,
            'CommentByUserId' => $comment->CommentByUserId,
            'Content' => $comment->Content,
            'ParentCommentId' => $comment->ParentCommentId,
            'CreatedAt' => $comment->CreatedAt,
            'UpdatedAt' => $comment->UpdatedAt,
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
                ->map(fn ($reply) => $this->mapComment($reply))
                ->values(),
        ];
    }
}
