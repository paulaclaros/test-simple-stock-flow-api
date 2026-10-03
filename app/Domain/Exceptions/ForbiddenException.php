<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

class ForbiddenException extends DomainException
{
    public function __construct(string $message = "No tiene permisos para realizar esta operación.")
    {
        parent::__construct($message);
    }
}
