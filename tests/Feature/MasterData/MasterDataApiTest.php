<?php

namespace Tests\Feature\MasterData;

use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterDataApiTest extends TestCase
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

    public function test_group_crud_flow(): void
    {
        $actor = $this->createPerson('actor@example.com');
        $token = $this->makeJwt($actor->Id, ['group.create', 'group.update', 'group.view', 'group.delete']);

        $create = $this->postJson('/api/groups/group', [
            'GroupName' => 'Team A',
            'Description' => 'Desc',
            'Amount' => 10000,
            'MinimumAmount' => 100,
            'MaximumAmount' => 100000,
            'GroupStatus' => 2,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $create->assertOk()->assertContent('true');

        $groupId = (int) \DB::table('Groups')->where('GroupName', 'Team A')->value('Id');

        $index = $this->getJson('/api/groups/group', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $index->assertOk()->assertJsonFragment(['GroupName' => 'Team A']);

        $detail = $this->getJson('/api/groups/group/'.$groupId, [
            'Authorization' => 'Bearer '.$token,
        ]);

        $detail->assertOk()->assertJsonPath('Id', $groupId);

        $update = $this->putJson('/api/groups/group', [
            'id' => $groupId,
            'GroupName' => 'Team A+',
            'Description' => 'Desc2',
            'Amount' => 12000,
            'MinimumAmount' => 200,
            'MaximumAmount' => 120000,
            'GroupStatus' => 3,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $update->assertOk()->assertContent('true');

        $delete = $this->deleteJson('/api/groups/group', [
            'GroupId' => $groupId,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $delete->assertOk()->assertContent('true');

        $this->assertDatabaseHas('Groups', [
            'Id' => $groupId,
            'IsDeleted' => true,
        ]);
    }

    public function test_person_crud_flow(): void
    {
        $actor = $this->createPerson('manager@example.com');
        $token = $this->makeJwt($actor->Id, ['person.view', 'person.update']);

        $create = $this->postJson('/api/persons/person', [
            'Name' => 'Person A',
            'Sex' => 1,
            'Email' => 'person-a@example.com',
            'AvatarUrl' => 'https://example.com/a.png',
            'DateOfBirth' => now()->subYears(20)->toDateString(),
            'PhoneNumber' => '0999',
            'Address' => 'Address A',
            'Password' => 'secret123',
            'BankID' => 'VCB',
            'BankAccountNumber' => '12345',
            'BankName' => 'Vietcombank',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $create->assertOk()->assertContent('true');

        $personId = (int) \DB::table('Persons')->where('Email', 'person-a@example.com')->value('Id');

        $index = $this->getJson('/api/persons/person', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $index->assertOk()->assertJsonFragment(['Email' => 'person-a@example.com']);

        $detail = $this->getJson('/api/persons/person/'.$personId, [
            'Authorization' => 'Bearer '.$token,
        ]);

        $detail->assertOk()->assertJsonPath('Id', $personId);

        $update = $this->putJson('/api/persons/person', [
            'Id' => $personId,
            'Name' => 'Person A+',
            'Email' => 'person-a@example.com',
            'PhoneNumber' => '0888',
            'Address' => 'Address B',
            'DateOfBirth' => now()->subYears(21)->toDateString(),
            'ProfilePictureUrl' => 'https://example.com/b.png',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $update->assertOk()->assertContent('true');

        $delete = $this->deleteJson('/api/persons/person', [
            'Id' => $personId,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $delete->assertOk()->assertContent('true');

        $this->assertDatabaseHas('Persons', [
            'Id' => $personId,
            'IsDeleted' => true,
        ]);
    }

    public function test_appsetting_create_show_update_and_public(): void
    {
        $actor = $this->createPerson('owner@example.com');
        $token = $this->makeJwt($actor->Id, ['task.view']);

        $create = $this->postJson('/api/appsettings', [
            'AvatarUrl' => 'https://example.com/logo.png',
            'AppName' => 'Finance App',
            'ContactEmail' => 'contact@example.com',
            'DomainWebsite' => 'https://example.com',
            'ConfigJson' => '{"theme":"dark"}',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $create->assertOk()->assertContent('true');

        $show = $this->getJson('/api/appsettings', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $show->assertOk()->assertJsonPath('AppName', 'Finance App');

        $public = $this->getJson('/api/appsettings/public');
        $public->assertOk()->assertJsonPath('AppName', 'Finance App');

        $id = (int) \DB::table('AppSettings')->where('AppName', 'Finance App')->value('Id');

        $update = $this->putJson('/api/appsettings', [
            'Id' => $id,
            'AvatarUrl' => 'https://example.com/logo2.png',
            'AppName' => 'Finance App 2',
            'ContactEmail' => 'support@example.com',
            'DomainWebsite' => 'https://example.org',
            'ConfigJson' => '{"theme":"light"}',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $update->assertOk()->assertContent('true');

        $this->assertDatabaseHas('AppSettings', [
            'Id' => $id,
            'AppName' => 'Finance App 2',
        ]);
    }

    private function createPerson(string $email): Person
    {
        return Person::query()->create([
            'Name' => 'Actor',
            'Sex' => 2,
            'Email' => $email,
            'AvatarUrl' => 'https://example.com/avatar.png',
            'DateOfBirth' => now()->subYears(30),
            'PhoneNumber' => '0909',
            'Address' => 'HN',
            'Password' => Hash::make('secret123'),
            'BankID' => 'VCB',
            'BankAccountNumber' => '12345',
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
