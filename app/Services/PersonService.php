<?php

namespace App\Services;

use App\Models\Person;
use Illuminate\Support\Facades\Hash;

class PersonService
{
    public function all()
    {
        return Person::query()
            ->notDeleted()
            ->orderByDesc('Id')
            ->get();
    }

    public function findById(int $id): Person
    {
        return Person::query()->notDeleted()->findOrFail($id);
    }

    public function create(array $attributes, int $actorId): Person
    {
        return Person::query()->create([
            'Name' => $attributes['Name'],
            'Sex' => (int) ($attributes['Sex'] ?? 0),
            'Email' => $attributes['Email'],
            'AvatarUrl' => $attributes['AvatarUrl'] ?? '',
            'DateOfBirth' => $attributes['DateOfBirth'] ?? null,
            'PhoneNumber' => $attributes['PhoneNumber'] ?? '',
            'Address' => $attributes['Address'] ?? '',
            'Password' => Hash::make($attributes['Password']),
            'BankID' => $attributes['BankID'] ?? '',
            'BankAccountNumber' => $attributes['BankAccountNumber'] ?? '',
            'BankName' => $attributes['BankName'] ?? '',
            'IsDeleted' => false,
            'CreatedBy' => $actorId,
            'UpdatedBy' => null,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
            'DeleteAt' => null,
        ]);
    }

    public function update(array $attributes, int $actorId): Person
    {
        $person = Person::query()->notDeleted()->findOrFail((int) $attributes['Id']);

        $person->fill([
            'Name' => $attributes['Name'],
            'Email' => $attributes['Email'],
            'PhoneNumber' => $attributes['PhoneNumber'] ?? '',
            'Address' => $attributes['Address'] ?? '',
            'DateOfBirth' => $attributes['DateOfBirth'] ?? null,
            'AvatarUrl' => $attributes['ProfilePictureUrl'] ?? $person->AvatarUrl,
            'UpdatedBy' => $actorId,
            'UpdatedAt' => now(),
        ]);

        $person->save();

        return $person;
    }

    public function delete(int $id, int $actorId): void
    {
        $person = Person::query()->findOrFail($id);

        $person->fill([
            'IsDeleted' => true,
            'DeleteBy' => $actorId,
            'DeleteAt' => now(),
            'UpdatedBy' => $actorId,
            'UpdatedAt' => now(),
        ]);

        $person->save();
    }
}
