<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Entities\User;

interface JwtTokenServiceInterface
{
    public function generateToken(User $user): string;

    /**
     * @return array{sub: string, username: string, role: string, fullName: string}|null
     */
    public function validateToken(string $token): ?array;
}
