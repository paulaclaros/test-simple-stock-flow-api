<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Application\DTOs\AuthResponseDTO;
use App\Application\DTOs\LoginDTO;
use App\Application\DTOs\RegisterUserDTO;
use App\Domain\Entities\User;

/**
 * Puerto de Entrada: Autenticación y Registro (Authenticate).
 * Definido en ARQUITECTURA-ONION.md (E-01, E-02, T-06).
 */
interface Authenticate
{
    public function login(LoginDTO $dto): AuthResponseDTO;
    public function register(RegisterUserDTO $dto): User;
}
