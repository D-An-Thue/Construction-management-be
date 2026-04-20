<?php

namespace App\Http\Controllers\Api;

use App\Services\UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends BaseApiController
{
    public function __construct(private readonly UploadService $uploadService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'max:30720'],
        ]);

        return response()->json(
            $this->uploadService->uploadMany($validated['files'], $this->currentUserId() ?? 0)
        );
    }
}
