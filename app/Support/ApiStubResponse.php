<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiStubResponse
{
    public static function notMigrated(string $action): JsonResponse
    {
        return response()->json([
            'status' => 501,
            'title' => 'Not Implemented',
            'detail' => sprintf('%s is not migrated yet.', $action),
        ], 501);
    }
}
