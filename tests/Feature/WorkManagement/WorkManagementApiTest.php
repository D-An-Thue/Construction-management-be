<?php

namespace Tests\Feature\WorkManagement;

use App\Models\Group;
use App\Models\Person;
use App\Models\PersonGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkManagementApiTest extends TestCase
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

    public function test_task_subtask_and_comment_flow(): void
    {
        $actor = $this->createPerson('task-actor@example.com');
        $group = $this->createGroup($actor->Id);
        $this->createMembership($group->Id, $actor->Id, true);

        $token = $this->makeJwt($actor->Id, ['task.view', 'task.create', 'task.update', 'task.delete']);

        $createTask = $this->postJson('/api/tasks/task', [
            'taskTitle' => 'Prepare report',
            'taskDescription' => 'Monthly closing report',
            'groupId' => $group->Id,
            'priority' => 2,
            'referenceGroupUserId' => [],
            'attachLink' => [],
            'ticketReferenceIds' => [],
            'cost' => 100,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $createTask->assertOk()->assertContent('true');

        $taskId = (int) \DB::table('TaskCollections')->where('TaskTitle', 'Prepare report')->value('Id');

        $indexTask = $this->getJson('/api/tasks/task?groupId='.$group->Id, [
            'Authorization' => 'Bearer '.$token,
        ]);

        $indexTask->assertOk()->assertJsonFragment(['TaskTitle' => 'Prepare report']);

        $createSubTask = $this->postJson('/api/tasks/'.$taskId.'/subtasks', [
            'title' => 'Collect invoices',
            'description' => 'Collect all invoices from group',
            'type' => 1,
            'status' => 0,
            'priority' => 1,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $createSubTask->assertOk()->assertContent('true');

        $subTaskId = (int) \DB::table('SubTasks')->where('TaskId', $taskId)->value('Id');

        $comment = $this->postJson('/api/tasks/task/'.$taskId.'/comments', [
            'content' => 'Need this done by Friday',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $comment->assertOk()->assertContent('true');

        $detail = $this->getJson('/api/tasks/task/'.$taskId.'/details', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $detail->assertOk()
            ->assertJsonPath('Id', $taskId)
            ->assertJsonFragment(['Title' => 'Collect invoices'])
            ->assertJsonFragment(['Content' => 'Need this done by Friday']);

        $updateTask = $this->putJson('/api/tasks/task', [
            'id' => $taskId,
            'taskTitle' => 'Prepare report updated',
            'taskDescription' => 'Monthly closing report updated',
            'groupId' => $group->Id,
            'status' => 1,
            'priority' => 3,
            'referenceGroupUserId' => [],
            'attachLink' => [],
            'ticketReferenceIds' => [],
            'cost' => 120,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $updateTask->assertOk()->assertContent('true');

        $deleteSubTask = $this->deleteJson('/api/tasks/'.$taskId.'/subtasks', [
            'id' => $subTaskId,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $deleteSubTask->assertOk()->assertContent('true');

        $deleteTask = $this->deleteJson('/api/tasks/task', [
            'id' => $taskId,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $deleteTask->assertOk()->assertContent('true');

        $this->assertDatabaseHas('TaskCollections', [
            'Id' => $taskId,
            'IsDeleted' => true,
        ]);
    }

    public function test_ticket_dashboard_transaction_and_product_flow(): void
    {
        $actor = $this->createPerson('ticket-actor@example.com');
        $group = $this->createGroup($actor->Id);
        $membership = $this->createMembership($group->Id, $actor->Id, true);

        \DB::table('Transactions')->insert([
            'id' => '1001',
            'userID' => $actor->Id,
            'TypeTransaction' => 1,
            'Description' => 'seed tx',
            'When' => now(),
            'TransactionId' => (string) \Illuminate\Support\Str::uuid(),
        ]);

        $token = $this->makeJwt($actor->Id, [
            'ticket.view',
            'ticket.create',
            'ticket.approve',
            'ticket.delete',
            'task.view',
            'product.view',
            'product.create',
            'product.update',
            'product.delete',
            'transaction.view',
        ]);

        $createTicket = $this->postJson('/api/tickets/ticket', [
            'groupId' => $group->Id,
            'title' => 'Request reimbursement',
            'description' => 'Travel expenses',
            'assignToUserId' => $membership->Id,
            'priority' => 2,
            'ticketType' => 1,
            'amount' => 500,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $createTicket->assertOk()->assertContent('true');

        $ticketId = (int) \DB::table('Tickets')->where('Title', 'Request reimbursement')->value('Id');

        $approve = $this->putJson('/api/tickets/approve', [
            'ticketId' => $ticketId,
            'status' => 1,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $approve->assertOk()->assertContent('true');

        $dashboard = $this->getJson('/api/dashboard', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $dashboard->assertOk()
            ->assertJsonStructure(['Tasks', 'Tickets'])
            ->assertJsonPath('Tickets.Total', 1);

        $transaction = $this->getJson('/api/transactions/transaction/1001/details', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $transaction->assertOk()->assertJsonPath('id', '1001');

        $createProduct = $this->postJson('/api/products/product', [
            'productCode' => 'PR-01',
            'productName' => 'Product A',
            'unitName' => 'Box',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $createProduct->assertOk()->assertContent('true');

        $productId = (int) \DB::table('Products')->where('ProductCode', 'PR-01')->value('Id');

        $showProduct = $this->getJson('/api/products/product/'.$productId, [
            'Authorization' => 'Bearer '.$token,
        ]);

        $showProduct->assertOk()->assertJsonPath('ProductName', 'Product A');

        $updateProduct = $this->putJson('/api/products/product', [
            'id' => $productId,
            'productCode' => 'PR-01',
            'productName' => 'Product A+',
            'unitName' => 'Pack',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $updateProduct->assertOk()->assertContent('true');

        $deleteProduct = $this->deleteJson('/api/products/product', [
            'id' => $productId,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $deleteProduct->assertOk()->assertContent('true');

        $this->assertDatabaseHas('Products', [
            'Id' => $productId,
            'IsDeleted' => true,
        ]);
    }

    public function test_upload_endpoint_stores_file_and_returns_metadata(): void
    {
        Storage::fake('local');

        $actor = $this->createPerson('upload-actor@example.com');
        $token = $this->makeJwt($actor->Id, ['task.create']);

        $response = $this->postJson('/api/Uploads/Upload', [
            'file' => UploadedFile::fake()->image('avatar.jpg'),
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk()->assertJsonStructure([
            'Id',
            'OriginalName',
            'StoredName',
            'Disk',
            'Path',
            'MimeType',
            'Size',
            'Url',
        ]);

        $path = (string) $response->json('Path');
        Storage::disk('local')->assertExists($path);
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

    private function createGroup(int $actorId): Group
    {
        return Group::query()->create([
            'GroupName' => 'Team Work',
            'Description' => 'Work group',
            'Amount' => 10000,
            'MinimumAmount' => 100,
            'MaximumAmount' => 20000,
            'GroupStatus' => 2,
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

    private function createMembership(int $groupId, int $personId, bool $isAdmin): PersonGroup
    {
        return PersonGroup::query()->create([
            'GroupId' => $groupId,
            'PersonId' => $personId,
            'NickName' => 'ActorNick',
            'JoinDate' => now(),
            'IsAdmin' => $isAdmin,
            'JoinEnums' => 1,
            'TransactionId' => (string) \Illuminate\Support\Str::uuid(),
            'IsDeleted' => false,
            'CreatedBy' => $personId,
            'UpdatedBy' => null,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
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
