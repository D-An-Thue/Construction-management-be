<?php

namespace Tests\Feature\Auth;

use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('jwt.issuer', 'CREASOFT');
        config()->set('jwt.audience', 'Client');
        config()->set('jwt.signature', 'test-signature-key');
        config()->set('jwt.leeway', 0);
    }

    public function test_login_returns_token_and_profile_payload(): void
    {
        $person = Person::query()->create([
            'Name' => 'Test User',
            'Sex' => 2,
            'Email' => 'tester@example.com',
            'AvatarUrl' => 'https://example.com/avatar.png',
            'DateOfBirth' => now()->subYears(20),
            'PhoneNumber' => '0123456789',
            'Address' => 'HCM',
            'Password' => Hash::make('secret123'),
            'BankID' => 'VCB',
            'BankAccountNumber' => '123456789',
            'BankName' => 'Vietcombank',
            'IsDeleted' => false,
            'CreatedBy' => 0,
            'UpdatedBy' => 0,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => now(),
            'DeleteAt' => null,
        ]);

        $role = Role::query()->create([
            'RoleName' => 'Admin',
            'Description' => 'admin',
            'IsDeleted' => false,
            'CreatedBy' => 0,
            'UpdatedBy' => 0,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
            'DeleteAt' => null,
        ]);

        $permission = Permission::query()->create([
            'PermissionCode' => 'group.view',
            'PermissionName' => 'Xem nhóm',
            'Module' => 'Group',
            'IsDeleted' => false,
            'CreatedBy' => 0,
            'UpdatedBy' => 0,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
            'DeleteAt' => null,
        ]);

        RolePermission::query()->create([
            'RoleId' => $role->Id,
            'PermissionId' => $permission->Id,
        ]);

        UserRole::query()->create([
            'PersonId' => $person->Id,
            'RoleId' => $role->Id,
        ]);

        $response = $this->postJson('/api/authentications/login', [
            'Email' => 'tester@example.com',
            'Password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'Token',
                'RefreshToken',
                'UserId',
                'Email',
                'FullName',
                'ExpiresAt',
                'Avatar',
            ])
            ->assertJsonPath('Email', 'tester@example.com')
            ->assertJsonPath('UserId', $person->Id)
            ->assertJsonPath('FullName', 'Test User');
    }

    public function test_register_creates_person_record(): void
    {
        $response = $this->postJson('/api/authentications/register', [
            'Name' => 'Register User',
            'Email' => 'register@example.com',
            'Password' => 'secret123',
            'ConfirmPassword' => 'secret123',
            'PhoneNumber' => '0123456789',
            'Address' => 'HCM',
            'DateOfBirth' => now()->subYears(22)->toDateString(),
            'Sex' => 1,
            'BankId' => 'ACB',
            'BankAccountNumber' => '12345',
            'BankName' => 'ACB',
        ]);

        $response->assertOk()
            ->assertJsonPath('Success', true)
            ->assertJsonPath('Message', 'Đăng ký thành công');

        $this->assertDatabaseHas('Persons', [
            'Email' => 'register@example.com',
            'Name' => 'Register User',
            'IsDeleted' => false,
        ]);
    }

    public function test_me_returns_current_user_profile(): void
    {
        $person = Person::query()->create([
            'Name' => 'Current User',
            'Sex' => 2,
            'Email' => 'me@example.com',
            'AvatarUrl' => 'https://example.com/me.png',
            'DateOfBirth' => now()->subYears(25),
            'PhoneNumber' => '0987654321',
            'Address' => 'Da Nang',
            'Password' => Hash::make('secret123'),
            'BankID' => 'TCB',
            'BankAccountNumber' => '998877',
            'BankName' => 'Techcombank',
            'IsDeleted' => false,
            'CreatedBy' => 0,
            'UpdatedBy' => 0,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => now(),
            'DeleteAt' => null,
        ]);

        $role = Role::query()->create([
            'RoleName' => 'Member',
            'Description' => 'member',
            'IsDeleted' => false,
            'CreatedBy' => 0,
            'UpdatedBy' => 0,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
            'DeleteAt' => null,
        ]);

        UserRole::query()->create([
            'PersonId' => $person->Id,
            'RoleId' => $role->Id,
        ]);

        $token = $this->makeJwt([
            'sub' => (string) $person->Id,
            'email' => $person->Email,
            'name' => $person->Name,
            'permission' => ['task.view'],
        ]);

        $response = $this->getJson('/api/authentications/me', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk()
            ->assertJsonPath('Id', $person->Id)
            ->assertJsonPath('Email', 'me@example.com')
            ->assertJsonPath('Name', 'Current User')
            ->assertJsonStructure([
                'Id',
                'Name',
                'Email',
                'AvatarUrl',
                'PhoneNumber',
                'Address',
                'DateOfBirth',
                'Sex',
                'Roles',
                'Permissions',
            ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function makeJwt(array $payload = []): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $now = time();

        $claims = array_merge([
            'iss' => (string) config('jwt.issuer'),
            'aud' => (string) config('jwt.audience'),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + 3600,
        ], $payload);

        $encodedHeader = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $encodedPayload = $this->base64UrlEncode(json_encode($claims, JSON_THROW_ON_ERROR));

        $signature = hash_hmac(
            'sha256',
            $encodedHeader.'.'.$encodedPayload,
            (string) config('jwt.signature'),
            true
        );

        $encodedSignature = $this->base64UrlEncode($signature);

        return $encodedHeader.'.'.$encodedPayload.'.'.$encodedSignature;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
