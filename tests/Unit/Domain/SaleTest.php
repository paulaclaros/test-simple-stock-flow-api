<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Entities\Sale;
use App\Domain\Entities\SaleItem;
use App\Domain\Exceptions\BusinessRuleValidationException;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\Quantity;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SaleTest extends TestCase
{
    public function test_can_create_valid_sale(): void
    {
        $item1 = new SaleItem(
            id: 'item-001',
            saleId: 'sale-001',
            productId: 'prod-001',
            productName: 'Taladro 650W',
            unitPrice: Money::fromDecimal(100000.0),
            quantity: Quantity::fromInt(2)
        );

        $item2 = new SaleItem(
            id: 'item-002',
            saleId: 'sale-001',
            productId: 'prod-002',
            productName: 'Broca Concreto',
            unitPrice: Money::fromDecimal(15000.0),
            quantity: Quantity::fromInt(1)
        );

        $sale = new Sale(
            id: 'sale-001',
            userId: 'user-001',
            items: [$item1, $item2],
            createdAt: new DateTimeImmutable()
        );

        $this->assertSame('sale-001', $sale->getId());
        $this->assertCount(2, $sale->getItems());
        // RN-12: Total es exactamente la suma (100000 * 2 + 15000 = 215000)
        $this->assertSame(215000.0, $sale->getTotal()->getAmount());
    }

    public function test_rn_04_sale_must_have_at_least_one_item(): void
    {
        $this->expectException(BusinessRuleValidationException::class);
        new Sale(
            id: 'sale-001',
            userId: 'user-001',
            items: [],
            createdAt: new DateTimeImmutable()
        );
    }
}
