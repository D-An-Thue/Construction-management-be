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

        return response()->json($people);
    }

    public function show(int $idPerson): JsonResponse
    {
        $person = $this->personService->findById($idPerson);

        return response()->json([
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
            'Name' => ['required', 'string'],
            'Sex' => ['nullable', 'integer'],
            'Email' => ['required', 'email'],
            'AvatarUrl' => ['nullable', 'string'],
            'DateOfBirth' => ['nullable', 'date'],
            'PhoneNumber' => ['nullable', 'string'],
            'Address' => ['nullable', 'string'],
            'Password' => ['required', 'string', 'min:6'],
            'BankID' => ['nullable', 'string'],
            'BankAccountNumber' => ['nullable', 'string'],
            'BankName' => ['nullable', 'string'],
        ]);

        $this->personService->create($validated, $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Id' => ['required', 'integer'],
            'Name' => ['required', 'string'],
            'Email' => ['required', 'email'],
            'PhoneNumber' => ['nullable', 'string'],
            'Address' => ['nullable', 'string'],
            'DateOfBirth' => ['nullable', 'date'],
            'ProfilePictureUrl' => ['nullable', 'string'],
        ]);

        $this->personService->update($validated, $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Id' => ['required', 'integer'],
        ]);

        $this->personService->delete((int) $validated['Id'], $this->currentUserId() ?? 0);

        return response()->json(true);
    }
}
