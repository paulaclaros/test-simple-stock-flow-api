<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class AuthTokenDTO
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $tokenType,
        public readonly int $expiresIn,
        public readonly array $user
    ) {
    }
}
