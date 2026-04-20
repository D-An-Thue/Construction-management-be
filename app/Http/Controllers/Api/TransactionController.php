<?php

namespace App\Http\Controllers\Api;

use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;

class TransactionController extends BaseApiController
{
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    public function show(int|string $id): JsonResponse
    {
        return response()->json(
            $this->transactionService->detailByTransactionId((string) $id)
        );
    }
}
