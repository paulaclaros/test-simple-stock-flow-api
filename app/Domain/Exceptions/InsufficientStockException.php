<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

class InsufficientStockException extends DomainException
{
    public function __construct(string $message = "Existencias insuficientes para completar la venta.")
    {
        parent::__construct($message);
    }
}
