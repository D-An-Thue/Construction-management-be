<?php

namespace App\Services;

use App\Models\PasswordResetToken;
use App\Models\Person;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * @return array<string, mixed>
     */
    public function login(string $email, string $password): array
    {
        $person = Person::query()
            ->notDeleted()
            ->where('Email', $email)
            ->with([
                'roles' => fn ($query) => $query->notDeleted(),
                'roles.permissions' => fn ($query) => $query->notDeleted(),
            ])
            ->first();

        if (! $person || ! Hash::check($password, (string) $person->Password)) {
            throw new AuthenticationException('Tài khoản hoặc mật khẩu không chính xác.');
        }

        return [
            'person' => $person,
            ...$this->extractRolesAndPermissions($person),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function me(int $personId): array
    {
        $person = Person::query()
            ->notDeleted()
            ->whereKey($personId)
            ->with([
                'roles' => fn ($query) => $query->notDeleted(),
                'roles.permissions' => fn ($query) => $query->notDeleted(),
            ])
            ->first();

        if (! $person) {
            throw new AuthenticationException('User not found');
        }

        return [
            'person' => $person,
            ...$this->extractRolesAndPermissions($person),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function register(array $data): array
    {
        $existing = Person::query()
            ->notDeleted()
            ->where('Email', $data['email'])
            ->first();

        if ($existing) {
            return [
                'Success' => false,
                'Message' => 'Email đã được sử dụng',
                'UserId' => null,
            ];
        }

        $person = Person::query()->create([
            'Name' => $data['name'],
            'Sex' => (int) ($data['sex'] ?? 3),
            'Email' => $data['email'],
            'AvatarUrl' => $data['avatarUrl'] ?? 'https://via.placeholder.com/150',
            'DateOfBirth' => $data['dateOfBirth'] ?? now()->subYears(18),
            'PhoneNumber' => $data['phoneNumber'],
            'Address' => $data['address'],
            'Password' => Hash::make($data['password']),
            'BankID' => $data['bankId'] ?? '',
            'BankAccountNumber' => $data['bankAccountNumber'] ?? '',
            'BankName' => $data['bankName'] ?? '',
            'IsDeleted' => false,
            'CreatedBy' => 0,
            'UpdatedBy' => 0,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => now(),
            'DeleteAt' => null,
        ]);

        return [
            'Success' => true,
            'Message' => 'Đăng ký thành công',
            'UserId' => $person->Id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forgotPassword(string $email): array
    {
        $person = Person::query()
            ->notDeleted()
            ->where('Email', $email)
            ->first();

        if (! $person) {
            return [
                'Success' => true,
                'Message' => 'Nếu email tồn tại, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu',
                'ResetToken' => null,
            ];
        }

        $token = Str::random(64);

        PasswordResetToken::query()->updateOrCreate(
            ['email' => $email],
            ['token' => hash('sha256', $token), 'created_at' => now()]
        );

        return [
            'Success' => true,
            'Message' => 'Hướng dẫn đặt lại mật khẩu đã được gửi đến email của bạn',
            'ResetToken' => app()->isLocal() ? $token : null,
        ];
    }

    /**
     * @return array{roles: array<int, string>, permissions: array<int, string>}
     */
    private function extractRolesAndPermissions(Person $person): array
    {
        return [
            'roles' => $person->roles->pluck('RoleName')->values()->all(),
            'permissions' => $person->roles
                ->flatMap(fn ($role) => $role->permissions->pluck('PermissionCode'))
                ->unique()
                ->values()
                ->all(),
        ];
    }
}
