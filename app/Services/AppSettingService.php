<?php

namespace App\Services;

use App\Models\AppSetting;

class AppSettingService
{
    public function current(): ?AppSetting
    {
        return AppSetting::query()
            ->notDeleted()
            ->orderByDesc('Id')
            ->first();
    }

    public function create(array $attributes, int $actorId): bool
    {
        AppSetting::query()->create([
            'AvatarUrl' => $attributes['AvatarUrl'] ?? null,
            'AppName' => $attributes['AppName'],
            'ContactEmail' => $attributes['ContactEmail'] ?? null,
            'DomainWebsite' => $attributes['DomainWebsite'] ?? null,
            'ConfigJson' => $attributes['ConfigJson'] ?? null,
            'IsDeleted' => false,
            'CreatedBy' => $actorId,
            'UpdatedBy' => null,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
            'DeleteAt' => null,
        ]);

        return true;
    }

    public function update(array $attributes, int $actorId): bool
    {
        $existing = AppSetting::query()->find((int) $attributes['Id']);

        if (! $existing) {
            return false;
        }

        $existing->fill([
            'AvatarUrl' => $attributes['AvatarUrl'] ?? null,
            'AppName' => $attributes['AppName'],
            'ContactEmail' => $attributes['ContactEmail'] ?? null,
            'DomainWebsite' => $attributes['DomainWebsite'] ?? null,
            'ConfigJson' => $attributes['ConfigJson'] ?? null,
            'UpdatedBy' => $actorId,
            'UpdatedAt' => now(),
        ]);

        $existing->save();

        return true;
    }
}
