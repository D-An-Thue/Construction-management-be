<?php

namespace App\Http\Controllers\Api;

use App\Services\AppSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppSettingController extends BaseApiController
{
    public function __construct(private readonly AppSettingService $appSettingService)
    {
    }

    public function show(): JsonResponse
    {
        $setting = $this->appSettingService->current();

        if (! $setting) {
            return response()->json(null, 204);
        }

        return response()->json([
            'Id' => $setting->Id,
            'AvatarUrl' => $setting->AvatarUrl,
            'AppName' => $setting->AppName,
            'ContactEmail' => $setting->ContactEmail,
            'DomainWebsite' => $setting->DomainWebsite,
            'ConfigJson' => $setting->ConfigJson,
            'CreatedAt' => $setting->CreatedAt,
            'UpdatedAt' => $setting->UpdatedAt,
        ]);
    }

    public function public(): JsonResponse
    {
        return $this->show();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'AvatarUrl' => ['nullable', 'string'],
            'AppName' => ['required', 'string'],
            'ContactEmail' => ['nullable', 'string'],
            'DomainWebsite' => ['nullable', 'string'],
            'ConfigJson' => ['nullable', 'string'],
        ]);

        $result = $this->appSettingService->create($validated, $this->currentUserId() ?? 0);

        return response()->json($result);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Id' => ['required', 'integer'],
            'AvatarUrl' => ['nullable', 'string'],
            'AppName' => ['required', 'string'],
            'ContactEmail' => ['nullable', 'string'],
            'DomainWebsite' => ['nullable', 'string'],
            'ConfigJson' => ['nullable', 'string'],
        ]);

        $result = $this->appSettingService->update($validated, $this->currentUserId() ?? 0);

        return response()->json($result);
    }
}
