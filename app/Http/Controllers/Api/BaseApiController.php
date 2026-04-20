<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiStubResponse;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

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


    protected function jsonResponse(mixed $payload, int $status = 200): JsonResponse
    {
        return response()->json($this->normalizeResponseKeys($payload), $status);
    }

    protected function normalizeResponseKeys(mixed $data): mixed
    {
        if ($data instanceof DateTimeInterface) {
            return $data->format('Y-m-d H:i:s');
        }

        if ($data instanceof Arrayable) {
            return $this->normalizeResponseKeys($data->toArray());
        }

        if (is_array($data)) {
            $isList = array_is_list($data);
            $result = [];

            foreach ($data as $key => $value) {
                if ($isList) {
                    $result[] = $this->normalizeResponseKeys($value);
                    continue;
                }

                $normalizedKey = is_string($key) ? Str::camel($key) : $key;
                $result[$normalizedKey] = $this->normalizeResponseKeys($value);
            }

            return $result;
        }

        return $data;
    }

    public function __call(string $method, array $parameters): JsonResponse
    {
        return ApiStubResponse::notMigrated(static::class.'@'.$method);
    }
}
