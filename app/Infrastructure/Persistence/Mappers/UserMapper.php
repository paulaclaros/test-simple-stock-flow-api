<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\User;
use App\Infrastructure\Persistence\Models\UserModel;
use DateTimeImmutable;

final class UserMapper
{
    public static function toDomain(UserModel $model): User
    {
        return new User(
            id: (string) $model->id,
            username: (string) $model->username,
            fullName: (string) $model->full_name,
            role: (string) $model->role,
            passwordHash: (string) $model->password_hash,
            createdAt: $model->created_at ? DateTimeImmutable::createFromMutable($model->created_at) : null
        );
    }
}
