<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\AuthTokenDTO;
use App\Application\DTOs\LoginDTO;
use App\Application\Services\JwtTokenServiceInterface;
use App\Application\Services\PasswordHasherInterface;
use App\Domain\Entities\User;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Repositories\UserRepositoryInterface;

final class LoginUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly JwtTokenServiceInterface $jwtTokenService
    ) {
    }

    public function execute(LoginDTO $dto): AuthTokenDTO
    {
        $normalizedUsername = User::normalizeUsername($dto->username);
        $user = $this->userRepository->findByUsername($normalizedUsername);

        // CA-07.2: No revelar si falló el usuario o la contraseña
        if ($user === null) {
            throw new UnauthorizedException("Credenciales inválidas.");
        }

        if (!$this->passwordHasher->verify($dto->password, $user->getPasswordHash())) {
            throw new UnauthorizedException("Credenciales inválidas.");
        }

        $token = $this->jwtTokenService->generateToken($user);

        return new AuthTokenDTO(
            accessToken: $token,
            tokenType: "Bearer",
            expiresIn: 3600,
            user: [
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                "fullName" => $user->getFullName(),
                "role" => $user->getRole(),
            ]
        );
    }
}
