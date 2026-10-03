<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\RegisterUserDTO;
use App\Application\Services\PasswordHasherInterface;
use App\Domain\Entities\User;
use App\Domain\Exceptions\BusinessRuleValidationException;
use App\Domain\Repositories\UserRepositoryInterface;
use Ramsey\Uuid\Uuid;

final class RegisterUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher
    ) {
    }

    public function execute(RegisterUserDTO $dto): string
    {
        $normalizedUsername = User::normalizeUsername($dto->username);

        // RN-10 / CA-07.6: Dos usuarios no pueden compartir nombre de usuario
        $existing = $this->userRepository->findByUsername($normalizedUsername);
        if ($existing !== null) {
            throw new BusinessRuleValidationException("El nombre de usuario ya está en uso.");
        }

        $passwordHash = $this->passwordHasher->hash($dto->password);
        $userId = Uuid::uuid4()->toString();

        $user = new User(
            id: $userId,
            username: $normalizedUsername,
            fullName: $dto->fullName,
            role: $dto->role,
            passwordHash: $passwordHash
        );

        $this->userRepository->save($user);

        return $user->getId();
    }
}
