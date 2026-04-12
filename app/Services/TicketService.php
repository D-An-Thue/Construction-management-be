<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class TicketService
{
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
            'Status' => (int) ($attributes['Status'] ?? 0),
            'Priority' => (int) ($attributes['Priority'] ?? 1),
            'TicketType' => (int) ($attributes['TicketType'] ?? 0),
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
            'Status' => (int) ($attributes['Status'] ?? 0),
            'Priority' => (int) ($attributes['Priority'] ?? 1),
            'TicketType' => (int) ($attributes['TicketType'] ?? 0),
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
            'Pending' => $tickets->where('Status', 0)->count(),
            'Approved' => $tickets->where('Status', 1)->count(),
            'Rejected' => $tickets->where('Status', 2)->count(),
        ];
    }
}
