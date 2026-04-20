<?php

namespace App\Services;

use App\Models\Transaction;

class TransactionService
{
    public function detailByTransactionId(string $transactionId): array
    {
        return Transaction::query()
            ->where('TransactionId', $transactionId)
            ->with('personNavigation')
            ->get()
            ->map(fn ($transaction) => [
                'id' => $transaction->id,
                'user' => [
                    'Id' => $transaction->personNavigation?->Id,
                    'Name' => $transaction->personNavigation?->Name,
                    'Sex' => $transaction->personNavigation?->Sex,
                    'Email' => $transaction->personNavigation?->Email,
                    'AvatarUrl' => $transaction->personNavigation?->AvatarUrl,
                ],
                'TypeTransaction' => (int) $transaction->TypeTransaction,
                'Description' => $transaction->Description,
                'When' => $transaction->When,
                'TransactionId' => $transaction->TransactionId,
            ])
            ->values()
            ->all();
    }
}
