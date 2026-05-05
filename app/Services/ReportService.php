<?php

namespace App\Services;

use App\Models\SubTask;
use App\Models\TaskCollection;

class ReportService
{
    public function overdueTasks(): array
    {
        $now = now();

        $overdueTasks = TaskCollection::query()
            ->notDeleted()
            ->whereNotNull('DueDate')
            ->where('DueDate', '<', $now)
            ->whereNotIn('Status', [2, 3])
            ->with(['assignToUser.person'])
            ->orderBy('DueDate')
            ->get();

        $overdueSubTasks = SubTask::query()
            ->notDeleted()
            ->whereNotNull('DueDate')
            ->where('DueDate', '<', $now)
            ->whereNotIn('Status', [2, 3])
            ->with(['assignToUser.person', 'task'])
            ->orderBy('DueDate')
            ->get();

        return [
            'totalOverdueTasks' => $overdueTasks->count(),
            'totalOverdueSubTasks' => $overdueSubTasks->count(),
            'overdueTasks' => $overdueTasks->map(fn ($task) => [
                'id' => (int) $task->Id,
                'taskTitle' => (string) $task->TaskTitle,
                'status' => (int) $task->Status,
                'statusName' => $this->statusName((int) $task->Status),
                'priority' => (int) $task->Priority,
                'priorityName' => $this->priorityName((int) $task->Priority),
                'dueDate' => optional($task->DueDate)?->format('Y-m-d\\TH:i:s'),
                'overdueDays' => max(1, (int) $task->DueDate?->copy()->startOfDay()->diffInDays($now->copy()->startOfDay())),
                'assignToUserName' => $task->assignToUser?->person?->Name,
                'groupId' => (int) $task->GroupId,
            ])->values()->all(),
            'overdueSubTasks' => $overdueSubTasks->map(fn ($subTask) => [
                'id' => (int) $subTask->Id,
                'taskId' => (int) $subTask->TaskId,
                'parentTaskTitle' => (string) ($subTask->task?->TaskTitle ?? ''),
                'title' => (string) $subTask->Title,
                'status' => (int) $subTask->Status,
                'statusName' => $this->statusName((int) $subTask->Status),
                'priority' => (int) $subTask->Priority,
                'priorityName' => $this->priorityName((int) $subTask->Priority),
                'dueDate' => optional($subTask->DueDate)?->format('Y-m-d\\TH:i:s'),
                'overdueDays' => max(1, (int) $subTask->DueDate?->copy()->startOfDay()->diffInDays($now->copy()->startOfDay())),
                'assignToUserName' => $subTask->assignToUser?->person?->Name,
            ])->values()->all(),
        ];
    }

    public function dailyTasksByStatus(string $fromDate, string $toDate): array
    {
        $taskRows = TaskCollection::query()
            ->notDeleted()
            ->whereBetween('CreatedAt', [$fromDate, $toDate])
            ->selectRaw('DATE(CreatedAt) as TaskDate, Status, COUNT(*) as Count')
            ->groupByRaw('DATE(CreatedAt), Status')
            ->orderBy('TaskDate')
            ->get();

        $subTaskRows = SubTask::query()
            ->notDeleted()
            ->whereBetween('CreatedAt', [$fromDate, $toDate])
            ->selectRaw('DATE(CreatedAt) as TaskDate, Status, COUNT(*) as Count')
            ->groupByRaw('DATE(CreatedAt), Status')
            ->orderBy('TaskDate')
            ->get();

        $tasksByDay = $taskRows->groupBy('TaskDate')->map(function ($items, $date) {
            return [
                'date' => (string) $date,
                'totalCount' => (int) $items->sum('Count'),
                'statusCounts' => $items->map(fn ($item) => [
                    'status' => (int) $item->Status,
                    'statusName' => $this->statusName((int) $item->Status),
                    'count' => (int) $item->Count,
                ])->values()->all(),
            ];
        })->values()->all();

        $subTasksByDay = $subTaskRows->groupBy('TaskDate')->map(function ($items, $date) {
            return [
                'date' => (string) $date,
                'totalCount' => (int) $items->sum('Count'),
                'statusCounts' => $items->map(fn ($item) => [
                    'status' => (int) $item->Status,
                    'statusName' => $this->statusName((int) $item->Status),
                    'count' => (int) $item->Count,
                ])->values()->all(),
            ];
        })->values()->all();

        return [
            'tasksByDay' => $tasksByDay,
            'subTasksByDay' => $subTasksByDay,
        ];
    }

    private function statusName(int $status): string
    {
        return match ($status) {
            0 => 'Pending',
            1 => 'InProgress',
            2 => 'Completed',
            3 => 'Cancelled',
            4 => 'ReOpen',
            default => 'Unknown',
        };
    }

    private function priorityName(int $priority): string
    {
        return match ($priority) {
            1 => 'Low',
            2 => 'Medium',
            3 => 'High',
            default => (string) $priority,
        };
    }
}
