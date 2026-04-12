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
            'GroupId' => ['required', 'integer'],
            'Title' => ['required', 'string'],
            'Description' => ['nullable', 'string'],
            'AssignToUserID' => ['nullable', 'integer'],
            'Priority' => ['nullable', 'integer'],
            'TicketType' => ['nullable', 'integer'],
            'Amount' => ['nullable', 'numeric'],
        ]);

        $this->ticketService->create($validated, $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Id' => ['required', 'integer'],
            'GroupId' => ['required', 'integer'],
            'Title' => ['required', 'string'],
            'Description' => ['nullable', 'string'],
            'ApproveForUserId' => ['nullable', 'integer'],
            'AssignToUserID' => ['nullable', 'integer'],
            'Status' => ['nullable', 'integer'],
            'Priority' => ['nullable', 'integer'],
            'TicketType' => ['nullable', 'integer'],
            'Amount' => ['nullable', 'numeric'],
        ]);

        $this->ticketService->update($validated, $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function approve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'TicketId' => ['required', 'integer'],
            'Status' => ['required', 'integer'],
        ]);

        $this->ticketService->approve((int) $validated['TicketId'], (int) $validated['Status'], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Id' => ['required', 'integer'],
        ]);

        $this->ticketService->delete((int) $validated['Id'], $this->currentUserId() ?? 0);

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
