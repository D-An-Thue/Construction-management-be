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
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $result = $this->authService->login($validated['email'], $validated['password']);
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

        return $this->jsonResponse([
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

        return $this->jsonResponse([
            'id' => $person->Id,
            'name' => $person->Name ?? '',
            'email' => $person->Email ?? '',
            'avatarUrl' => $person->AvatarUrl ?? '',
            'phoneNumber' => $person->PhoneNumber ?? '',
            'address' => $person->Address ?? '',
            'dateOfBirth' => $person->DateOfBirth,
            'sex' => (int) $person->Sex,
            'roles' => $result['roles'],
            'permissions' => $result['permissions'],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
            'confirmPassword' => ['required', 'same:password'],
            'phoneNumber' => ['required', 'string'],
            'address' => ['required', 'string'],
            'dateOfBirth' => ['nullable', 'date'],
            'sex' => ['nullable', 'integer'],
            'avatarUrl' => ['nullable', 'string'],
            'bankId' => ['nullable', 'string'],
            'bankAccountNumber' => ['nullable', 'string'],
            'bankName' => ['nullable', 'string'],
        ]);

        $response = $this->authService->register([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phoneNumber' => $validated['phoneNumber'],
            'address' => $validated['address'],
            'dateOfBirth' => $validated['dateOfBirth'] ?? null,
            'sex' => $validated['sex'] ?? 3,
            'avatarUrl' => $validated['avatarUrl'] ?? null,
            'bankId' => $validated['bankId'] ?? null,
            'bankAccountNumber' => $validated['bankAccountNumber'] ?? null,
            'bankName' => $validated['bankName'] ?? null,
        ]);

        return $this->jsonResponse($response);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        return $this->jsonResponse($this->authService->forgotPassword($validated['email']));
    }
}
