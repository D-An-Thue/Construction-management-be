<?php

namespace Tests\Feature\Smoke;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiFoundationSmokeTest extends TestCase
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

    public function test_health_endpoint_returns_expected_shape(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure(['status', 'service', 'timestamp']);
    }

    public function test_protected_endpoint_without_token_returns_401_problem_details(): void
    {
        $response = $this->getJson('/api/groups/group');

        $response->assertUnauthorized()
            ->assertJsonPath('status', 401)
            ->assertJsonStructure(['status', 'title', 'detail']);
    }

    public function test_protected_endpoint_with_missing_permission_returns_403_problem_details(): void
    {
        $token = $this->makeJwt([
            'permission' => ['task.view'],
        ]);

        $response = $this->getJson('/api/groups/group', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertForbidden()
            ->assertJsonPath('status', 403)
            ->assertJsonStructure(['status', 'title', 'detail']);
    }

    public function test_compatibility_route_forgot_pasword_exists(): void
    {
        $response = $this->postJson('/api/authentications/forgot-pasword', [
            'Email' => 'ghost@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('Success', true)
            ->assertJsonPath('Message', 'Nếu email tồn tại, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu');
    }

    public function test_compatibility_route_uploads_upload_exists_with_token(): void
    {
        $token = $this->makeJwt([
            'permission' => ['task.create'],
        ]);

        $response = $this->postJson('/api/Uploads/Upload', [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 422)
            ->assertJsonStructure(['status', 'title', 'detail']);
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
