<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTOs\AuthResponseDTO;
use App\Application\DTOs\LoginDTO;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\Ports\Inbound\Authenticate;
use App\Application\Services\JwtTokenServiceInterface;
use App\Application\Services\PasswordHasherInterface;
use App\Domain\Entities\User;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Repositories\UserRepositoryInterface;
use Ramsey\Uuid\Uuid;

/**
 * Caso de Uso: AuthenticationService (T-06, E-01, E-02).
 * Gestiona inicio de sesión y registro de usuarios (solo admins pueden registrar).
 */
final class AuthenticationService implements Authenticate
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly JwtTokenServiceInterface $jwtTokenService
    ) {
    }

    public function login(LoginDTO $dto): AuthResponseDTO
    {
        $normalizedUsername = strtolower(trim($dto->username));
        $user = $this->userRepository->findByUsername($normalizedUsername);

        if ($user === null || !$this->passwordHasher->verify($dto->password, $user->getPasswordHash())) {
            throw new UnauthorizedException("Credenciales inválidas.");
        }

        $token = $this->jwtTokenService->generate([
            'sub' => $user->getId(),
            'username' => $user->getUsername(),
            'role' => $user->getRole(),
        ]);

        return new AuthResponseDTO(
            token: $token,
            userId: $user->getId(),
            username: $user->getUsername(),
            role: $user->getRole()
        );
    }

    public function register(RegisterUserDTO $dto): User
    {
        $user = new User(
            id: Uuid::uuid4()->toString(),
            username: $dto->username,
            passwordHash: $this->passwordHasher->hash($dto->password),
            role: $dto->role
        );

        $this->userRepository->save($user);

        return $user;
    }
}
