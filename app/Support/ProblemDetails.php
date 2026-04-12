<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ProblemDetails
{
    public static function json(int $status, string $detail, ?string $title = null): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'title' => $title ?? request()->header('X-Trace-Id') ?? (string) Str::uuid(),
            'detail' => $detail,
        ], $status);
    }
}
