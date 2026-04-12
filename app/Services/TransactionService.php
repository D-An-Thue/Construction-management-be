<?php

namespace App\Services;

use App\Models\Transaction;

class TransactionService
{
    public function detail(string $id): Transaction
    {
        return Transaction::query()->findOrFail($id);
    }
}
