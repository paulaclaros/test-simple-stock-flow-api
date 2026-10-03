<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class RegisterUserDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $password,
        public readonly string $fullName,
        public readonly string $role
    ) {
    }
}
