<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiStubResponse;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    protected function currentUserId(): ?int
    {
        $claims = request()->attributes->get('jwt.claims', []);

        if (! is_array($claims)) {
            return null;
        }

        $sub = $claims['sub'] ?? null;

        if (is_int($sub)) {
            return $sub;
        }

        if (is_string($sub) && ctype_digit($sub)) {
            return (int) $sub;
        }

        return null;
    }

    public function __call(string $method, array $parameters): JsonResponse
    {
        return ApiStubResponse::notMigrated(static::class.'@'.$method);
    }
}
