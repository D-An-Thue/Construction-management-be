<?php

namespace App\Services;

use App\Models\TaskCollection;
use App\Services\TicketService;

class DashboardService
{
    public function summaryByUser(int $personId): array
    {
        $taskQuery = TaskCollection::query()
            ->notDeleted()
            ->where(function ($query) use ($personId) {
                $query->where('CreatedBy', $personId)
                    ->orWhere('AssignToUserId', $personId)
                    ->orWhereHas('group.members', function ($memberQuery) use ($personId) {
                        $memberQuery->where('PersonId', $personId)
                            ->where('IsDeleted', false);
                    });
            });

        $tasks = (clone $taskQuery)->get();

        return [
            'Tasks' => [
                'Total' => $tasks->count(),
                'Todo' => $tasks->where('Status', 0)->count(),
                'InProgress' => $tasks->where('Status', 1)->count(),
                'Done' => $tasks->where('Status', 2)->count(),
            ],
            'Tickets' => app(TicketService::class)->ticketStatsByUser($personId),
        ];
    }

    public function taskStatsByUser(int $personId): array
    {
        $tasks = TaskCollection::query()
            ->notDeleted()
            ->where(function ($query) use ($personId) {
                $query->where('CreatedBy', $personId)
                    ->orWhere('AssignToUserId', $personId)
                    ->orWhereHas('group.members', function ($memberQuery) use ($personId) {
                        $memberQuery->where('PersonId', $personId)
                            ->where('IsDeleted', false);
                    });
            })
            ->get();

        return [
            'Total' => $tasks->count(),
            'Todo' => $tasks->where('Status', 0)->count(),
            'InProgress' => $tasks->where('Status', 1)->count(),
            'Done' => $tasks->where('Status', 2)->count(),
        ];
    }
}
