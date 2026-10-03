<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Exceptions\BusinessRuleValidationException;
use DateTimeImmutable;

final class User
{
    public const ROLE_ADMIN = "admin";
    public const ROLE_SELLER = "seller";

    private string $id;
    private string $username;
    private string $fullName;
    private string $role;
    private string $passwordHash;
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        string $username,
        string $fullName,
        string $role,
        string $passwordHash,
        ?DateTimeImmutable $createdAt = null
    ) {
        $normalizedUsername = self::normalizeUsername($username);
        if ($normalizedUsername === "") {
            throw new BusinessRuleValidationException("El nombre de usuario es obligatorio.");
        }

        $fullNameTrimmed = trim($fullName);
        if ($fullNameTrimmed === "") {
            throw new BusinessRuleValidationException("El nombre completo es obligatorio.");
        }

        if (!self::isValidRole($role)) {
            throw new BusinessRuleValidationException("El rol debe ser 'admin' o 'seller'.");
        }

        $this->id = $id;
        $this->username = $normalizedUsername;
        $this->fullName = $fullNameTrimmed;
        $this->role = $role;
        $this->passwordHash = $passwordHash;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public static function normalizeUsername(string $username): string
    {
        return strtolower(trim($username));
    }

    public static function isValidRole(string $role): bool
    {
        return in_array($role, [self::ROLE_ADMIN, self::ROLE_SELLER], true);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSeller(): bool
    {
        return $this->role === self::ROLE_SELLER;
    }
}
