<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\Services\JwtTokenServiceInterface;
use App\Domain\Entities\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

final class FirebaseJwtTokenService implements JwtTokenServiceInterface
{
    private string $secretKey;
    private int $ttlSeconds;

    public function __construct(?string $secretKey = null, int $ttlSeconds = 3600)
    {
        $this->secretKey = $secretKey ?: (string) env('JWT_SECRET', 'simple_stock_flow_super_secret_jwt_key_3413974_adso');
        $this->ttlSeconds = $ttlSeconds;
    }

    public function generateToken(User $user): string
    {
        $now = time();
        $payload = [
            'iss' => 'simple-stock-flow-api',
            'sub' => $user->getId(),
            'username' => $user->getUsername(),
            'role' => $user->getRole(),
            'fullName' => $user->getFullName(),
            'iat' => $now,
            'exp' => $now + $this->ttlSeconds,
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return (array) $decoded;
        } catch (Throwable) {
            return null;
        }
    }
}
