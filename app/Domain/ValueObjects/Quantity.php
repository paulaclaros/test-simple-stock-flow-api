<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Exceptions\BusinessRuleValidationException;

final class Quantity
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new BusinessRuleValidationException("La cantidad debe ser mayor que cero.");
        }

        $this->value = $value;
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(Quantity $other): bool
    {
        return $this->value === $other->value;
    }
}
