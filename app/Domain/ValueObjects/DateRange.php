<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Exceptions\BusinessRuleValidationException;
use DateTimeImmutable;

final class DateRange
{
    private DateTimeImmutable $from;
    private DateTimeImmutable $to;

    public function __construct(DateTimeImmutable $from, DateTimeImmutable $to)
    {
        if ($to < $from) {
            throw new BusinessRuleValidationException("La fecha final no puede ser anterior a la inicial.");
        }

        $this->from = $from;
        $this->to = $to;
    }

    public static function create(DateTimeImmutable $from, DateTimeImmutable $to): self
    {
        return new self($from, $to);
    }

    public function getFrom(): DateTimeImmutable
    {
        return $this->from;
    }

    public function getTo(): DateTimeImmutable
    {
        return $this->to;
    }

    /**
     * D-C2: from inclusivo, to exclusivo (from <= sold_at < to)
     */
    public function includes(DateTimeImmutable $date): bool
    {
        return $date >= $this->from && $date < $this->to;
    }
}
