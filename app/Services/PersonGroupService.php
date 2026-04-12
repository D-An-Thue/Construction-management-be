<?php

namespace App\Services;

use App\Models\PersonGroup;
use Illuminate\Support\Str;

class PersonGroupService
{
    public function create(array $attributes, int $actorId): bool
    {
        PersonGroup::query()->create([
            'GroupId' => (int) $attributes['GroupId'],
            'PersonId' => (int) $attributes['PersonId'],
            'NickName' => $attributes['NickName'] ?? '',
            'JoinDate' => $attributes['JoinDate'] ?? now(),
            'IsAdmin' => (bool) ($attributes['IsAdmin'] ?? false),
            'JoinEnums' => (int) ($attributes['JoinEnums'] ?? 1),
            'TransactionId' => (string) Str::uuid(),
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

    public function update(array $attributes, int $actorId): void
    {
        $personGroup = PersonGroup::query()->notDeleted()->findOrFail((int) $attributes['Id']);

        $personGroup->fill([
            'NickName' => $attributes['NickName'] ?? $personGroup->NickName,
            'JoinEnums' => (int) ($attributes['JoinEnums'] ?? $personGroup->JoinEnums),
            'UpdatedBy' => $actorId,
            'UpdatedAt' => now(),
        ]);

        $personGroup->save();
    }

    public function delete(int $id, int $actorId): void
    {
        $personGroup = PersonGroup::query()->notDeleted()->findOrFail($id);

        $personGroup->fill([
            'IsDeleted' => true,
            'DeleteBy' => $actorId,
            'DeleteAt' => now(),
            'UpdatedBy' => $actorId,
            'UpdatedAt' => now(),
        ]);

        $personGroup->save();
    }

    public function setAdmin(int $id, bool $isAdmin, int $actorId): void
    {
        $personGroup = PersonGroup::query()->notDeleted()->findOrFail($id);

        $personGroup->fill([
            'IsAdmin' => $isAdmin,
            'UpdatedBy' => $actorId,
            'UpdatedAt' => now(),
        ]);

        $personGroup->save();
    }

    public function setStatus(int $id, int $joinEnums, int $actorId): void
    {
        $personGroup = PersonGroup::query()->notDeleted()->findOrFail($id);

        $personGroup->fill([
            'JoinEnums' => $joinEnums,
            'UpdatedBy' => $actorId,
            'UpdatedAt' => now(),
        ]);

        $personGroup->save();
    }
}
