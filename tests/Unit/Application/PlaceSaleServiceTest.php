<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\DTOs\RegisterSaleDTO;
use App\Application\DTOs\RegisterSaleItemDTO;
use App\Application\Ports\Outbound\UnitOfWork;
use App\Application\UseCase\PlaceSaleService;
use App\Domain\Entities\Product;
use App\Domain\Exceptions\BusinessRuleValidationException;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\Quantity;
use PHPUnit\Framework\TestCase;

final class PlaceSaleServiceTest extends TestCase
{
    public function test_rn_04_throws_exception_if_no_items(): void
    {
        $saleRepo = $this->createMock(SaleRepositoryInterface::class);
        $prodRepo = $this->createMock(ProductRepositoryInterface::class);
        $uow = $this->createMock(UnitOfWork::class);

        $service = new PlaceSaleService($saleRepo, $prodRepo, $uow);

        $dto = new RegisterSaleDTO(
            sellerId: 'user-001',
            items: []
        );

        $this->expectException(BusinessRuleValidationException::class);
        $this->expectExceptionMessage("La venta debe tener al menos un ítem.");

        $service->execute($dto);
    }

    public function test_rn_05_throws_exception_if_duplicate_products_in_sale(): void
    {
        $saleRepo = $this->createMock(SaleRepositoryInterface::class);
        $prodRepo = $this->createMock(ProductRepositoryInterface::class);
        $uow = $this->createMock(UnitOfWork::class);

        $service = new PlaceSaleService($saleRepo, $prodRepo, $uow);

        $dto = new RegisterSaleDTO(
            sellerId: 'user-001',
            items: [
                new RegisterSaleItemDTO(productId: 'prod-001', quantity: 2),
                new RegisterSaleItemDTO(productId: 'prod-001', quantity: 3),
            ]
        );

        $this->expectException(BusinessRuleValidationException::class);
        $this->expectExceptionMessage("Un producto no puede repetirse dentro de la misma venta.");

        $service->execute($dto);
    }

    public function test_executes_sale_successfully_with_unit_of_work(): void
    {
        $saleRepo = $this->createMock(SaleRepositoryInterface::class);
        $prodRepo = $this->createMock(ProductRepositoryInterface::class);
        $uow = new class implements UnitOfWork {
            public function run(callable $operation): mixed {
                return $operation();
            }
        };

        $product = new Product(
            id: 'prod-001',
            name: 'Taladro 650W',
            categoryId: 'cat-001',
            price: Money::fromDecimal(100000.0),
            stock: Quantity::fromInt(10),
            imageUrl: null,
            isActive: true
        );

        $prodRepo->method('findActiveById')->willReturn($product);
        $saleRepo->expects($this->once())->method('save');

        $service = new PlaceSaleService($saleRepo, $prodRepo, $uow);

        $dto = new RegisterSaleDTO(
            sellerId: 'user-001',
            items: [
                new RegisterSaleItemDTO(productId: 'prod-001', quantity: 2),
            ]
        );

        $sale = $service->execute($dto);

        $this->assertNotNull($sale);
        $this->assertSame(8, $product->getStock()->getValue());
        $this->assertSame(200000.0, $sale->getTotal()->getAmount());
    }
}
