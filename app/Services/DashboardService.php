<?php

namespace App\Services;

use App\Models\Person;
use App\Models\TaskCollection;
use App\Models\Ticket;

class DashboardService
{
    public function __construct(private readonly TicketService $ticketService)
    {
    }

    public function summaryByUser(int $personId): array
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $fund = $this->ticketService->queryResolvedIncomeAndExpenseTotals();

        $todayTaskCount = TaskCollection::query()
            ->notDeleted()
            ->whereBetween('CreatedAt', [$todayStart, $todayEnd])
            ->count();

        $todayExpenses = Ticket::query()
            ->notDeleted()
            ->whereBetween('CreatedAt', [$todayStart, $todayEnd])
            ->groupBy('TicketType')
            ->selectRaw('TicketType, SUM(Amount) as TotalAmount, COUNT(*) as Count')
            ->get()
            ->map(fn ($row) => [
                'TicketType' => (int) $row->TicketType,
                'TicketTypeName' => $this->ticketTypeName((int) $row->TicketType),
                'TotalAmount' => (float) $row->TotalAmount,
                'Count' => (int) $row->Count,
            ])
            ->values()
            ->all();

        $totalUsers = Person::query()
            ->notDeleted()
            ->count();

        return [
            'TotalFundBalance' => $fund['fundBalance'],
            'TodayTaskCount' => $todayTaskCount,
            'TodayExpenses' => $todayExpenses,
            'TotalUsers' => $totalUsers,
        ];
    }

    public function ticketStatsByDateRange(string $fromDate, string $toDate): array
    {
        return Ticket::query()
            ->notDeleted()
            ->whereBetween('CreatedAt', [$fromDate, $toDate])
            ->groupBy('TicketType')
            ->selectRaw('TicketType, SUM(Amount) as TotalAmount, COUNT(*) as Count')
            ->get()
            ->map(fn ($row) => [
                'TicketType' => (int) $row->TicketType,
                'TicketTypeName' => $this->ticketTypeName((int) $row->TicketType),
                'TotalAmount' => (float) $row->TotalAmount,
                'Count' => (int) $row->Count,
            ])
            ->values()
            ->all();
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

    private function ticketTypeName(int $ticketType): string
    {
        return match ($ticketType) {
            1 => 'PHIEUCHI',
            2 => 'PHIEUNHAP',
            3 => 'DEXUAT',
            4 => 'CHUYENTIEN',
            5 => 'RUTTIEN',
            default => 'UNKNOWN',
        };
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
