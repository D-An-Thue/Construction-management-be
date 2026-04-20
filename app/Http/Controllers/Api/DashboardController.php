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
        return $this->jsonResponse(
            $this->dashboardService->summaryByUser($this->currentUserId() ?? 0)
        );
    }

    public function ticketStats(): JsonResponse
    {
        $validated = request()->validate([
            'fromDate' => ['required', 'date'],
            'toDate' => ['required', 'date'],
        ]);

        return $this->jsonResponse(
            $this->dashboardService->ticketStatsByDateRange($validated['fromDate'], $validated['toDate'])
        );
    }

    public function taskStats(): JsonResponse
    {
        $validated = request()->validate([
            'fromDate' => ['required', 'date'],
            'toDate' => ['required', 'date'],
        ]);

        return $this->jsonResponse(
            $this->dashboardService->taskStatsByDateRange($validated['fromDate'], $validated['toDate'])
        );
    }
}
