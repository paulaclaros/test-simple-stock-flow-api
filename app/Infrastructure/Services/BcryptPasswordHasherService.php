<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\Services\PasswordHasherInterface;

final class BcryptPasswordHasherService implements PasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    public function verify(string $plainPassword, string $hash): bool
    {
        return password_verify($plainPassword, $hash);
    }
}
