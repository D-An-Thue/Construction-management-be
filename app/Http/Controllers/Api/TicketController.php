<?php

namespace App\Http\Controllers\Api;

use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends BaseApiController
{
    public function __construct(private readonly TicketService $ticketService)
    {
    }

    public function index(): JsonResponse
    {
        $tickets = $this->ticketService->listByUser($this->currentUserId() ?? 0)
            ->map(fn ($ticket) => $this->mapTicket($ticket))
            ->values();

        return response()->json($tickets);
    }

    public function show(int $id): JsonResponse
    {
        $ticket = $this->ticketService->detail($id);

        return response()->json($this->mapTicket($ticket));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'groupId' => ['required', 'integer'],
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'assignToUserId' => ['nullable', 'integer'],
            'priority' => ['nullable', 'integer'],
            'ticketType' => ['nullable', 'integer'],
            'amount' => ['nullable', 'numeric'],
        ]);

        $this->ticketService->create([
            'GroupId' => $validated['groupId'],
            'Title' => $validated['title'],
            'Description' => $validated['description'] ?? null,
            'AssignToUserID' => $validated['assignToUserId'] ?? null,
            'Priority' => $validated['priority'] ?? null,
            'TicketType' => $validated['ticketType'] ?? null,
            'Amount' => $validated['amount'] ?? null,
        ], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'groupId' => ['required', 'integer'],
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'approveForUserId' => ['nullable', 'integer'],
            'assignToUserId' => ['nullable', 'integer'],
            'status' => ['nullable', 'integer'],
            'priority' => ['nullable', 'integer'],
            'ticketType' => ['nullable', 'integer'],
            'amount' => ['nullable', 'numeric'],
        ]);

        $this->ticketService->update([
            'Id' => $validated['id'],
            'GroupId' => $validated['groupId'],
            'Title' => $validated['title'],
            'Description' => $validated['description'] ?? null,
            'ApproveForUserId' => $validated['approveForUserId'] ?? null,
            'AssignToUserID' => $validated['assignToUserId'] ?? null,
            'Status' => $validated['status'] ?? null,
            'Priority' => $validated['priority'] ?? null,
            'TicketType' => $validated['ticketType'] ?? null,
            'Amount' => $validated['amount'] ?? null,
        ], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function approve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ticketId' => ['required', 'integer'],
            'status' => ['required', 'integer'],
        ]);

        $this->ticketService->approve((int) $validated['ticketId'], (int) $validated['status'], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);

        $this->ticketService->delete((int) $validated['id'], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    private function mapTicket(object $ticket): array
    {
        return [
            'Id' => $ticket->Id,
            'GroupId' => $ticket->GroupId,
            'Title' => $ticket->Title,
            'Description' => $ticket->Description,
            'ApproveForUserId' => $ticket->ApproveForUserId,
            'AssignToUserID' => $ticket->AssignToUserID,
            'Status' => $ticket->Status,
            'Priority' => $ticket->Priority,
            'TicketType' => $ticket->TicketType,
            'Amount' => $ticket->Amount,
            'TransactionId' => $ticket->TransactionId,
            'CreatedAt' => $ticket->CreatedAt,
            'UpdatedAt' => $ticket->UpdatedAt,
            'Group' => $ticket->group ? [
                'Id' => $ticket->group->Id,
                'GroupName' => $ticket->group->GroupName,
            ] : null,
        ];
    }
}
