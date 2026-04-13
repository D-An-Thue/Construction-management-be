<?php

namespace Tests\Feature\Role;

use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleApiTest extends TestCase
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

    public function test_permissions_endpoint_returns_permission_list(): void
    {
        $admin = $this->createAdmin();

        Permission::query()->create([
            'PermissionCode' => 'role.manage',
            'PermissionName' => 'Manage role',
            'Module' => 'Role',
            'IsDeleted' => false,
            'CreatedBy' => 0,
            'UpdatedBy' => 0,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
            'DeleteAt' => null,
        ]);

        $token = $this->makeJwt($admin->Id, ['role.manage']);

        $response = $this->getJson('/api/roles/permissions', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'PermissionCode' => 'role.manage',
            ]);
    }

    public function test_create_role_endpoint_returns_true(): void
    {
        $admin = $this->createAdmin();
        $token = $this->makeJwt($admin->Id, ['role.manage']);

        $response = $this->postJson('/api/roles', [
            'roleName' => 'Supervisor',
            'description' => 'Supervisor role',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk()->assertContent('true');

        $this->assertDatabaseHas('Roles', [
            'RoleName' => 'Supervisor',
            'IsDeleted' => false,
        ]);
    }

    public function test_assign_and_remove_permission_flow(): void
    {
        $admin = $this->createAdmin();
        $token = $this->makeJwt($admin->Id, ['role.manage']);

        $role = Role::query()->create([
            'RoleName' => 'Editor',
            'Description' => 'Editor role',
            'IsDeleted' => false,
            'CreatedBy' => 0,
            'UpdatedBy' => 0,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
            'DeleteAt' => null,
        ]);

        $permission = Permission::query()->create([
            'PermissionCode' => 'task.update',
            'PermissionName' => 'Update task',
            'Module' => 'Task',
            'IsDeleted' => false,
            'CreatedBy' => 0,
            'UpdatedBy' => 0,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
            'DeleteAt' => null,
        ]);

        $assign = $this->postJson('/api/roles/'.$role->Id.'/permissions', [
            'permissionId' => $permission->Id,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $assign->assertOk()->assertContent('true');

        $this->assertDatabaseHas('RolePermissions', [
            'RoleId' => $role->Id,
            'PermissionId' => $permission->Id,
        ]);

        $remove = $this->deleteJson('/api/roles/'.$role->Id.'/permissions/'.$permission->Id, [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $remove->assertOk()->assertContent('true');

        $this->assertDatabaseMissing('RolePermissions', [
            'RoleId' => $role->Id,
            'PermissionId' => $permission->Id,
        ]);
    }

    private function createAdmin(): Person
    {
        return Person::query()->create([
            'Name' => 'Admin User',
            'Sex' => 2,
            'Email' => 'admin@example.com',
            'AvatarUrl' => 'https://example.com/admin.png',
            'DateOfBirth' => now()->subYears(30),
            'PhoneNumber' => '0900000000',
            'Address' => 'HN',
            'Password' => Hash::make('secret123'),
            'BankID' => 'VCB',
            'BankAccountNumber' => '123123123',
            'BankName' => 'Vietcombank',
            'IsDeleted' => false,
            'CreatedBy' => 0,
            'UpdatedBy' => 0,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => now(),
            'DeleteAt' => null,
        ]);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function makeJwt(int $userId, array $permissions): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $now = time();

        $claims = [
            'iss' => (string) config('jwt.issuer'),
            'aud' => (string) config('jwt.audience'),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + 3600,
            'sub' => (string) $userId,
            'permission' => $permissions,
        ];

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
