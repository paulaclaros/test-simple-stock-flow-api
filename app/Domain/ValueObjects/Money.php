<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Exceptions\BusinessRuleValidationException;

final class Money
{
    private float $amount;
    private string $currency;

    public function __construct(float $amount, string $currency = "COP")
    {
        if ($amount < 0) {
            throw new BusinessRuleValidationException("El importe no puede ser negativo.");
        }

        if ($currency !== "COP") {
            throw new BusinessRuleValidationException("Solo se admite moneda COP.");
        }

        $this->amount = round($amount, 2);
        $this->currency = $currency;
    }

    public static function fromFloat(float $amount, string $currency = "COP"): self
    {
        return new self($amount, $currency);
    }

    public static function zero(string $currency = "COP"): self
    {
        return new self(0.0, $currency);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function plus(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new BusinessRuleValidationException("No se pueden operar importes en distintas monedas.");
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function times(int $multiplier): self
    {
        if ($multiplier < 0) {
            throw new BusinessRuleValidationException("El multiplicador no puede ser negativo.");
        }

        return new self($this->amount * $multiplier, $this->currency);
    }

    public function isGreaterThanZero(): bool
    {
        return $this->amount > 0;
    }

    public function equals(Money $other): bool
    {
        return $this->currency === $other->currency && abs($this->amount - $other->amount) < 0.0001;
    }
}
