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
            return $this->jsonResponse(null, 204);
        }

        return $this->jsonResponse([
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
            'avatarUrl' => ['nullable', 'string'],
            'appName' => ['required', 'string'],
            'contactEmail' => ['nullable', 'string'],
            'domainWebsite' => ['nullable', 'string'],
            'configJson' => ['nullable', 'string'],
        ]);

        $result = $this->appSettingService->create([
            'AvatarUrl' => $validated['avatarUrl'] ?? null,
            'AppName' => $validated['appName'],
            'ContactEmail' => $validated['contactEmail'] ?? null,
            'DomainWebsite' => $validated['domainWebsite'] ?? null,
            'ConfigJson' => $validated['configJson'] ?? null,
        ], $this->currentUserId() ?? 0);

        return $this->jsonResponse($result);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'avatarUrl' => ['nullable', 'string'],
            'appName' => ['required', 'string'],
            'contactEmail' => ['nullable', 'string'],
            'domainWebsite' => ['nullable', 'string'],
            'configJson' => ['nullable', 'string'],
        ]);

        $result = $this->appSettingService->update([
            'Id' => $validated['id'],
            'AvatarUrl' => $validated['avatarUrl'] ?? null,
            'AppName' => $validated['appName'],
            'ContactEmail' => $validated['contactEmail'] ?? null,
            'DomainWebsite' => $validated['domainWebsite'] ?? null,
            'ConfigJson' => $validated['configJson'] ?? null,
        ], $this->currentUserId() ?? 0);

        return $this->jsonResponse($result);
    }
}
