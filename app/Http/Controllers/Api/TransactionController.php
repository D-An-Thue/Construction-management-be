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
        $transaction = $this->transactionService->detail((string) $id);

        return response()->json([
            'id' => $transaction->id,
            'userID' => $transaction->userID,
            'TypeTransaction' => $transaction->TypeTransaction,
            'Description' => $transaction->Description,
            'When' => $transaction->When,
            'TransactionId' => $transaction->TransactionId,
        ]);
    }
}
