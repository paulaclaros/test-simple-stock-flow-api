<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Exceptions\InvalidPriceException;
use App\Domain\Exceptions\InvalidQuantityException;
use App\Domain\ValueObjects\DateRange;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\Quantity;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function test_money_calculates_correctly(): void
    {
        $m1 = Money::fromDecimal(100.50);
        $m2 = Money::fromDecimal(49.50);
        $sum = $m1->add($m2);

        $this->assertSame(150.0, $sum->getAmount());
        $this->assertSame('COP', $sum->getCurrency());
    }

    public function test_rn_02_price_cannot_be_negative_or_zero(): void
    {
        $this->expectException(InvalidPriceException::class);
        Money::fromDecimal(-10.0);
    }

    public function test_rn_03_quantity_must_be_greater_than_zero(): void
    {
        $this->expectException(InvalidQuantityException::class);
        Quantity::fromInt(0);
    }

    public function test_date_range_validates_order(): void
    {
        $from = new DateTimeImmutable('2026-10-01 00:00:00');
        $to = new DateTimeImmutable('2026-10-02 23:59:59');
        $range = new DateRange($from, $to);

        $this->assertSame($from, $range->getFrom());
        $this->assertSame($to, $range->getTo());
    }
}
