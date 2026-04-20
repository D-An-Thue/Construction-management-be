<?php

namespace App\Http\Controllers\Api;

use App\Services\PersonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonController extends BaseApiController
{
    public function __construct(private readonly PersonService $personService)
    {
    }

    public function index(): JsonResponse
    {
        $people = $this->personService->all()->map(function ($person) {
            return [
                'Id' => $person->Id,
                'Name' => $person->Name,
                'Sex' => (int) $person->Sex,
                'Email' => $person->Email,
                'AvatarUrl' => $person->AvatarUrl,
                'DateOfBirth' => $person->DateOfBirth,
                'PhoneNumber' => $person->PhoneNumber,
                'Address' => $person->Address,
                'BankId' => $person->BankID,
                'BankAccountNumber' => $person->BankAccountNumber,
                'BankName' => $person->BankName,
            ];
        })->values();

        return $this->jsonResponse($people);
    }

    public function show(int $idPerson): JsonResponse
    {
        $person = $this->personService->findById($idPerson);

        return $this->jsonResponse([
            'Id' => $person->Id,
            'Name' => $person->Name,
            'Sex' => (int) $person->Sex,
            'Email' => $person->Email,
            'AvatarUrl' => $person->AvatarUrl,
            'DateOfBirth' => $person->DateOfBirth,
            'PhoneNumber' => $person->PhoneNumber,
            'Address' => $person->Address,
            'BankId' => $person->BankID,
            'BankAccountNumber' => $person->BankAccountNumber,
            'BankName' => $person->BankName,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'sex' => ['nullable', 'integer'],
            'email' => ['required', 'email'],
            'avatarUrl' => ['nullable', 'string'],
            'dateOfBirth' => ['nullable', 'date'],
            'phoneNumber' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:6'],
            'bankId' => ['nullable', 'string'],
            'bankAccountNumber' => ['nullable', 'string'],
            'bankName' => ['nullable', 'string'],
        ]);

        $this->personService->create([
            'Name' => $validated['name'],
            'Sex' => $validated['sex'] ?? null,
            'Email' => $validated['email'],
            'AvatarUrl' => $validated['avatarUrl'] ?? null,
            'DateOfBirth' => $validated['dateOfBirth'] ?? null,
            'PhoneNumber' => $validated['phoneNumber'] ?? null,
            'Address' => $validated['address'] ?? null,
            'Password' => $validated['password'],
            'BankID' => $validated['bankId'] ?? null,
            'BankAccountNumber' => $validated['bankAccountNumber'] ?? null,
            'BankName' => $validated['bankName'] ?? null,
        ], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'phoneNumber' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'dateOfBirth' => ['nullable', 'date'],
            'profilePictureUrl' => ['nullable', 'string'],
        ]);

        $this->personService->update([
            'Id' => $validated['id'],
            'Name' => $validated['name'],
            'Email' => $validated['email'],
            'PhoneNumber' => $validated['phoneNumber'] ?? null,
            'Address' => $validated['address'] ?? null,
            'DateOfBirth' => $validated['dateOfBirth'] ?? null,
            'ProfilePictureUrl' => $validated['profilePictureUrl'] ?? null,
        ], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);

        $this->personService->delete((int) $validated['id'], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
    }
}
