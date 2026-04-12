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
        $summary = $this->dashboardService->summaryByUser($this->currentUserId() ?? 0);

        return response()->json($summary['Tickets']);
    }

    public function taskStats(): JsonResponse
    {
        return response()->json(
            $this->dashboardService->taskStatsByUser($this->currentUserId() ?? 0)
        );
    }
}
