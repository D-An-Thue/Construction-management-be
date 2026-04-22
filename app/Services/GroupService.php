<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Person;
use Illuminate\Database\Eloquent\Collection;

class GroupService
{
    public function listByUser(int $userId): Collection
    {
        return Group::query()
            ->notDeleted()
            ->where(function ($query) use ($userId) {
                $query->where('CreatedBy', $userId)
                    ->orWhereHas('members', function ($memberQuery) use ($userId) {
                        $memberQuery->where('PersonId', $userId)
                            ->where('IsDeleted', false);
                    });
            })
            ->with([
                'members' => fn ($query) => $query->where('IsDeleted', false),
            ])
            ->orderByDesc('Id')
            ->get();
    }

    public function detail(int $groupId): Group
    {
        return Group::query()
            ->notDeleted()
            ->with([
                'members' => fn ($query) => $query->where('IsDeleted', false)->with('person'),
            ])
            ->findOrFail($groupId);
    }

    public function create(array $attributes, int $actorId): Group
    {
        return Group::query()->create([
            'GroupName' => $attributes['GroupName'],
            'Description' => $attributes['Description'] ?? '',
            'Amount' => (int) ($attributes['Amount'] ?? 0),
            'MinimumAmount' => (int) ($attributes['MinimumAmount'] ?? 0),
            'MaximumAmount' => (int) ($attributes['MaximumAmount'] ?? 0),
            'GroupStatus' => (int) ($attributes['GroupStatus'] ?? 2),
            'TransactionId' => (string) \Illuminate\Support\Str::uuid(),
            'IsDeleted' => false,
            'CreatedBy' => $actorId,
            'UpdatedBy' => null,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
            'DeleteAt' => null,
        ]);
    }

    public function update(array $attributes, int $actorId): Group
    {
        $group = Group::query()->findOrFail((int) $attributes['id']);

        $group->fill([
            'GroupName' => $attributes['GroupName'],
            'Description' => $attributes['Description'] ?? '',
            'Amount' => (int) ($attributes['Amount'] ?? 0),
            'MinimumAmount' => (int) ($attributes['MinimumAmount'] ?? 0),
            'MaximumAmount' => (int) ($attributes['MaximumAmount'] ?? 0),
            'GroupStatus' => (int) ($attributes['GroupStatus'] ?? 2),
            'UpdatedBy' => $actorId,
            'UpdatedAt' => now(),
        ]);

        $group->save();

        return $group;
    }

    public function delete(int $groupId, int $actorId): void
    {
        $group = Group::query()->notDeleted()->findOrFail($groupId);

        $group->fill([
            'IsDeleted' => true,
            'DeleteBy' => $actorId,
            'DeleteAt' => now(),
            'UpdatedBy' => $actorId,
            'UpdatedAt' => now(),
        ]);

        $group->save();
    }

    /**
     * @param  array<int, string>  $fileUpload
     */
    public function appendConstructionDocuments(int $groupId, array $fileUpload, int $actorId): Group
    {
        $group = Group::query()->notDeleted()->findOrFail($groupId);

        $current = is_array($group->ConstructionDocuments) ? $group->ConstructionDocuments : [];
        $merged = array_values(array_unique(array_merge($current, $fileUpload), SORT_STRING));

        $group->fill([
            'ConstructionDocuments' => $merged,
            'UpdatedBy' => $actorId,
            'UpdatedAt' => now(),
        ]);

        $group->save();

        return $group;
    }

    public function memberPeople(int $groupId)
    {
        return $this->detail($groupId)->members
            ->filter(fn ($member) => $member->person !== null)
            ->map(function ($member) {
                return [
                    'Id' => $member->Id,
                    'GroupId' => $member->GroupId,
                    'PersonId' => $member->PersonId,
                    'person' => [
                        'Name' => $member->person->Name,
                        'Sex' => $member->person->Sex,
                        'Email' => $member->person->Email,
                        'AvatarUrl' => $member->person->AvatarUrl,
                        'DateOfBirth' => $member->person->DateOfBirth,
                        'PhoneNumber' => $member->person->PhoneNumber,
                    ],
                    'NickName' => $member->NickName,
                    'JoinDate' => $member->JoinDate,
                    'IsAdmin' => $member->IsAdmin,
                    'JoinEnums' => $member->JoinEnums,
                ];
            })
            ->values();
    }
}
