<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class TicketService
{
    private const STATUS_NEW = 1;
    private const STATUS_RESOLVED = 5;
    private const TYPE_EXPENSE = 1;
    private const TYPE_INCOME = 2;

    public function listByUser(int $personId): Collection
    {
        return Ticket::query()
            ->notDeleted()
            ->where(function ($query) use ($personId) {
                $query->where('CreatedBy', $personId)
                    ->orWhere('AssignToUserID', $personId)
                    ->orWhereHas('group.members', function ($memberQuery) use ($personId) {
                        $memberQuery->where('PersonId', $personId)
                            ->where('IsDeleted', false);
                    });
            })
            ->with(['group', 'assignToUser.person', 'approveForUser.person'])
            ->orderByDesc('Id')
            ->get();
    }

    public function detail(int $id): Ticket
    {
        return Ticket::query()
            ->notDeleted()
            ->with(['group', 'assignToUser.person', 'approveForUser.person'])
            ->findOrFail($id);
    }

    public function create(array $attributes, int $actorGroupId): bool
    {
        Ticket::query()->create([
            'GroupId' => (int) $attributes['GroupId'],
            'Title' => $attributes['Title'],
            'Description' => $attributes['Description'] ?? null,
            'ApproveForUserId' => $attributes['ApproveForUserId'] ?? null,
            'AssignToUserID' => $attributes['AssignToUserID'] ?? null,
            'Status' => (int) ($attributes['Status'] ?? self::STATUS_NEW),
            'Priority' => (int) ($attributes['Priority'] ?? 1),
            'TicketType' => (int) ($attributes['TicketType'] ?? self::TYPE_EXPENSE),
            'Amount' => (float) ($attributes['Amount'] ?? 0),
            'TransactionId' => (string) Str::uuid(),
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
        $ticket = Ticket::query()->notDeleted()->findOrFail((int) $attributes['Id']);

        $ticket->fill([
            'GroupId' => (int) $attributes['GroupId'],
            'Title' => $attributes['Title'],
            'Description' => $attributes['Description'] ?? null,
            'ApproveForUserId' => $attributes['ApproveForUserId'] ?? null,
            'AssignToUserID' => $attributes['AssignToUserID'] ?? null,
            'Status' => (int) ($attributes['Status'] ?? self::STATUS_NEW),
            'Priority' => (int) ($attributes['Priority'] ?? 1),
            'TicketType' => (int) ($attributes['TicketType'] ?? self::TYPE_EXPENSE),
            'Amount' => (float) ($attributes['Amount'] ?? 0),
            'UpdatedBy' => $actorGroupId,
            'UpdatedAt' => now(),
        ]);

        $ticket->save();
    }

    public function approve(int $ticketId, int $status, int $actorGroupId): void
    {
        $ticket = Ticket::query()->notDeleted()->findOrFail($ticketId);

        $ticket->fill([
            'Status' => $status,
            'ApproveForUserId' => $actorGroupId,
            'UpdatedBy' => $actorGroupId,
            'UpdatedAt' => now(),
        ]);

        $ticket->save();
    }

    public function delete(int $id, int $actorGroupId): void
    {
        $ticket = Ticket::query()->notDeleted()->findOrFail($id);

        $ticket->fill([
            'IsDeleted' => true,
            'DeleteBy' => $actorGroupId,
            'DeleteAt' => now(),
            'UpdatedBy' => $actorGroupId,
            'UpdatedAt' => now(),
        ]);

        $ticket->save();
    }

    public function ticketStatsByUser(int $personId): array
    {
        $tickets = $this->listByUser($personId);

        return [
            'Total' => $tickets->count(),
            'New' => $tickets->where('Status', 1)->count(),
            'Assigned' => $tickets->where('Status', 2)->count(),
            'InProgress' => $tickets->where('Status', 3)->count(),
            'OnHold' => $tickets->where('Status', 4)->count(),
            'Resolved' => $tickets->where('Status', 5)->count(),
            'Closed' => $tickets->where('Status', 6)->count(),
            'Reopened' => $tickets->where('Status', 7)->count(),
            'Cancelled' => $tickets->where('Status', 8)->count(),
        ];
    }

    public function queryResolvedIncomeAndExpenseTotals(): array
    {
        $resolvedTickets = Ticket::query()
            ->notDeleted()
            ->where('Status', self::STATUS_RESOLVED)
            ->get(['TicketType', 'Amount']);

        $income = (float) $resolvedTickets
            ->where('TicketType', self::TYPE_INCOME)
            ->sum('Amount');

        $expense = (float) $resolvedTickets
            ->where('TicketType', self::TYPE_EXPENSE)
            ->sum('Amount');

        return [
            'income' => $income,
            'expense' => $expense,
            'fundBalance' => $income - $expense,
        ];
    }
}
