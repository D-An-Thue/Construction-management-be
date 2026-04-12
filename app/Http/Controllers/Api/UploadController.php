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
            'file' => ['required', 'file', 'max:5120'],
        ]);

        return response()->json(
            $this->uploadService->upload($validated['file'], $this->currentUserId() ?? 0)
        );
    }
}
