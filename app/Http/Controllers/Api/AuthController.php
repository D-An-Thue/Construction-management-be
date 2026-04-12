<?php

namespace App\Http\Controllers\Api;

use App\Services\AuthService;
use App\Support\JwtToken;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends BaseApiController
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Email' => ['required', 'email'],
            'Password' => ['required', 'string'],
        ]);

        $result = $this->authService->login($validated['Email'], $validated['Password']);
        $person = $result['person'];
        $roles = $result['roles'];
        $permissions = $result['permissions'];

        $expiresAt = now()->addDay();

        $token = JwtToken::encode([
            'iss' => (string) config('jwt.issuer'),
            'aud' => (string) config('jwt.audience'),
            'iat' => now()->timestamp,
            'nbf' => now()->timestamp,
            'exp' => $expiresAt->timestamp,
            'sub' => (string) $person->Id,
            'email' => (string) $person->Email,
            'name' => (string) $person->Name,
            'role' => $roles,
            'permission' => $permissions,
        ]);

        return response()->json([
            'Token' => $token,
            'RefreshToken' => (string) \Illuminate\Support\Str::uuid(),
            'UserId' => $person->Id,
            'Email' => $person->Email,
            'FullName' => $person->Name,
            'ExpiresAt' => $expiresAt->toIso8601String(),
            'Avatar' => $person->AvatarUrl,
        ]);
    }

    public function me(): JsonResponse
    {
        $userId = $this->currentUserId();

        if (! $userId) {
            throw new AuthenticationException('User not authenticated');
        }

        $result = $this->authService->me($userId);
        $person = $result['person'];

        return response()->json([
            'Id' => $person->Id,
            'Name' => $person->Name ?? '',
            'Email' => $person->Email ?? '',
            'AvatarUrl' => $person->AvatarUrl ?? '',
            'PhoneNumber' => $person->PhoneNumber ?? '',
            'Address' => $person->Address ?? '',
            'DateOfBirth' => $person->DateOfBirth,
            'Sex' => (int) $person->Sex,
            'Roles' => $result['roles'],
            'Permissions' => $result['permissions'],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Name' => ['required', 'string', 'max:100'],
            'Email' => ['required', 'email'],
            'Password' => ['required', 'string', 'min:6', 'max:100'],
            'ConfirmPassword' => ['required', 'same:Password'],
            'PhoneNumber' => ['required', 'string'],
            'Address' => ['required', 'string'],
            'DateOfBirth' => ['nullable', 'date'],
            'Sex' => ['nullable', 'integer'],
            'AvatarUrl' => ['nullable', 'string'],
            'BankId' => ['nullable', 'string'],
            'BankAccountNumber' => ['nullable', 'string'],
            'BankName' => ['nullable', 'string'],
        ]);

        $response = $this->authService->register([
            'name' => $validated['Name'],
            'email' => $validated['Email'],
            'password' => $validated['Password'],
            'phoneNumber' => $validated['PhoneNumber'],
            'address' => $validated['Address'],
            'dateOfBirth' => $validated['DateOfBirth'] ?? null,
            'sex' => $validated['Sex'] ?? 3,
            'avatarUrl' => $validated['AvatarUrl'] ?? null,
            'bankId' => $validated['BankId'] ?? null,
            'bankAccountNumber' => $validated['BankAccountNumber'] ?? null,
            'bankName' => $validated['BankName'] ?? null,
        ]);

        return response()->json($response);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Email' => ['required', 'email'],
        ]);

        return response()->json($this->authService->forgotPassword($validated['Email']));
    }
}
