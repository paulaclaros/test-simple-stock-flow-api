<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Mappers\UserMapper;
use App\Infrastructure\Persistence\Models\UserModel;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        $model = UserModel::query()->find($id);
        if ($model === null) {
            return null;
        }

        return UserMapper::toDomain($model);
    }

    public function findByUsername(string $username): ?User
    {
        $normalized = User::normalizeUsername($username);
        $model = UserModel::query()->where('username', $normalized)->first();
        if ($model === null) {
            return null;
        }

        return UserMapper::toDomain($model);
    }

    public function save(User $user): void
    {
        UserModel::create([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'full_name' => $user->getFullName(),
            'role' => $user->getRole(),
            'password_hash' => $user->getPasswordHash(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
    }
}
