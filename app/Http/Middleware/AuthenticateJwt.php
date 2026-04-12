<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateJwt
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $claims = $this->decodeAndValidate($token);

        $request->attributes->set('jwt.claims', $claims);

        return $next($request);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeAndValidate(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new AuthenticationException('Invalid token.');
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $header = $this->decodeSegment($headerEncoded);
        $payload = $this->decodeSegment($payloadEncoded);
        $signature = $this->base64UrlDecode($signatureEncoded);

        if (! is_array($header) || ! is_array($payload) || $signature === false) {
            throw new AuthenticationException('Invalid token.');
        }

        if (($header['alg'] ?? null) !== 'HS256') {
            throw new AuthenticationException('Invalid token algorithm.');
        }

        $secret = (string) config('jwt.signature');

        if ($secret === '') {
            throw new AuthenticationException('JWT signature is not configured.');
        }

        $expectedSignature = hash_hmac('sha256', $headerEncoded.'.'.$payloadEncoded, $secret, true);

        if (! hash_equals($expectedSignature, $signature)) {
            throw new AuthenticationException('Invalid token signature.');
        }

        $now = time();
        $leeway = (int) config('jwt.leeway', 0);

        if (isset($payload['nbf']) && is_numeric($payload['nbf']) && $now + $leeway < (int) $payload['nbf']) {
            throw new AuthenticationException('Token is not active yet.');
        }

        if (isset($payload['exp']) && is_numeric($payload['exp']) && $now - $leeway >= (int) $payload['exp']) {
            throw new AuthenticationException('Token has expired.');
        }

        $issuer = (string) config('jwt.issuer');

        if ($issuer !== '' && ($payload['iss'] ?? null) !== $issuer) {
            throw new AuthenticationException('Invalid token issuer.');
        }

        $audience = (string) config('jwt.audience');

        if ($audience !== '') {
            $aud = $payload['aud'] ?? null;

            $isValidAudience = $aud === $audience
                || (is_array($aud) && in_array($audience, $aud, true));

            if (! $isValidAudience) {
                throw new AuthenticationException('Invalid token audience.');
            }
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeSegment(string $segment): ?array
    {
        $decoded = $this->base64UrlDecode($segment);

        if ($decoded === false) {
            return null;
        }

        $json = json_decode($decoded, true);

        return is_array($json) ? $json : null;
    }

    private function base64UrlDecode(string $data): string|false
    {
        $remainder = strlen($data) % 4;

        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'), true);
    }
}
