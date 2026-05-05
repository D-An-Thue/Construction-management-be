<?php

namespace App\Http\Controllers\Api;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends BaseApiController
{
    public function __construct(private readonly ReportService $reportService)
    {
    }

    public function overdueTasks(): JsonResponse
    {
        return $this->jsonResponse($this->reportService->overdueTasks());
    }

    public function dailyTasksByStatus(): JsonResponse
    {
        $validated = request()->validate([
            'fromDate' => ['required', 'date'],
            'toDate' => ['required', 'date', 'after_or_equal:fromDate'],
        ]);

        return $this->jsonResponse(
            $this->reportService->dailyTasksByStatus($validated['fromDate'], $validated['toDate'])
        );
    }
}
