<?php

namespace App\Services;

use App\Models\Person;
use App\Models\TaskCollection;

class DashboardService
{
    public function summaryByUser(int $personId): array
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $todayTaskCount = TaskCollection::query()
            ->notDeleted()
            ->whereBetween('CreatedAt', [$todayStart, $todayEnd])
            ->count();

        $totalUsers = Person::query()
            ->notDeleted()
            ->count();

        return [
            'TotalFundBalance' => 0,
            'TodayTaskCount' => $todayTaskCount,
            'TodayExpenses' => [],
            'TotalUsers' => $totalUsers,
        ];
    }

    public function taskStatsByDateRange(string $fromDate, string $toDate): array
    {
        return TaskCollection::query()
            ->notDeleted()
            ->whereBetween('CreatedAt', [$fromDate, $toDate])
            ->groupBy('Status')
            ->selectRaw('Status, COUNT(*) as Count')
            ->get()
            ->map(fn ($row) => [
                'Status' => (int) $row->Status,
                'StatusName' => $this->taskStatusName((int) $row->Status),
                'Count' => (int) $row->Count,
            ])
            ->values()
            ->all();
    }

    private function taskStatusName(int $status): string
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
}
