<?php

namespace App\Http\Controllers\Api;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends BaseApiController
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function summary(): JsonResponse
    {
        return response()->json(
            $this->dashboardService->summaryByUser($this->currentUserId() ?? 0)
        );
    }

    public function ticketStats(): JsonResponse
    {
        $validated = request()->validate([
            'fromDate' => ['required', 'date'],
            'toDate' => ['required', 'date'],
        ]);

        return response()->json(
            $this->dashboardService->ticketStatsByDateRange($validated['fromDate'], $validated['toDate'])
        );
    }

    public function taskStats(): JsonResponse
    {
        $validated = request()->validate([
            'fromDate' => ['required', 'date'],
            'toDate' => ['required', 'date'],
        ]);

        return response()->json(
            $this->dashboardService->taskStatsByDateRange($validated['fromDate'], $validated['toDate'])
        );
    }
}
