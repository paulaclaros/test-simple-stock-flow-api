<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Entities\Product;
use App\Domain\Exceptions\InsufficientStockException;
use App\Domain\Exceptions\InvalidPriceException;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\Quantity;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    public function test_can_create_valid_product(): void
    {
        $product = new Product(
            id: 'prod-001',
            name: 'Taladro 650W',
            categoryId: 'cat-001',
            price: Money::fromDecimal(150000.0),
            stock: Quantity::fromInt(10),
            imageUrl: null,
            isActive: true
        );

        $this->assertSame('prod-001', $product->getId());
        $this->assertSame('Taladro 650W', $product->getName());
        $this->assertSame(150000.0, $product->getPrice()->getAmount());
        $this->assertSame(10, $product->getStock()->getValue());
        $this->assertTrue($product->isActive());
    }

    public function test_rn_01_stock_cannot_be_negative(): void
    {
        $product = new Product(
            id: 'prod-001',
            name: 'Taladro 650W',
            categoryId: 'cat-001',
            price: Money::fromDecimal(150000.0),
            stock: Quantity::fromInt(5),
            imageUrl: null,
            isActive: true
        );

        $this->expectException(InsufficientStockException::class);
        $product->withdraw(Quantity::fromInt(6));
    }

    public function test_can_withdraw_available_stock(): void
    {
        $product = new Product(
            id: 'prod-001',
            name: 'Taladro 650W',
            categoryId: 'cat-001',
            price: Money::fromDecimal(150000.0),
            stock: Quantity::fromInt(10),
            imageUrl: null,
            isActive: true
        );

        $product->withdraw(Quantity::fromInt(4));
        $this->assertSame(6, $product->getStock()->getValue());
    }

    public function test_rn_08_deactivate_product(): void
    {
        $product = new Product(
            id: 'prod-001',
            name: 'Taladro 650W',
            categoryId: 'cat-001',
            price: Money::fromDecimal(150000.0),
            stock: Quantity::fromInt(10),
            imageUrl: null,
            isActive: true
        );

        $product->deactivate();
        $this->assertFalse($product->isActive());
    }
}
