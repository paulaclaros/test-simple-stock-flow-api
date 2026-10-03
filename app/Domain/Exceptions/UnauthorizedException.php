<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

class UnauthorizedException extends DomainException
{
    public function __construct(string $message = "Credenciales inválidas o no proporcionadas.")
    {
        parent::__construct($message);
    }
}
