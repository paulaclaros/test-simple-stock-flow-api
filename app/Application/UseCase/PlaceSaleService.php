<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTOs\RegisterSaleDTO;
use App\Application\Ports\Inbound\PlaceSale;
use App\Application\Ports\Outbound\UnitOfWork;
use App\Domain\Entities\Sale;
use App\Domain\Entities\SaleItem;
use App\Domain\Exceptions\BusinessRuleValidationException;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\ValueObjects\Quantity;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

/**
 * Caso de Uso: PlaceSaleService (T-10).
 * Orquesta el registro atómico de venta con descuento de stock e invariantes de negocio.
 */
final class PlaceSaleService implements PlaceSale
{
    public function __construct(
        private readonly SaleRepositoryInterface $saleRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly UnitOfWork $unitOfWork
    ) {
    }

    public function execute(RegisterSaleDTO $dto): Sale
    {
        if (empty($dto->items)) {
            throw new BusinessRuleValidationException("La venta debe tener al menos un ítem.");
        }

        // RN-05: Validar que ningún producto se repita en la misma venta
        $seen = [];
        foreach ($dto->items as $itemDto) {
            if (isset($seen[$itemDto->productId])) {
                throw new BusinessRuleValidationException("Un producto no puede repetirse dentro de la misma venta.");
            }
            $seen[$itemDto->productId] = true;
        }

        return $this->unitOfWork->run(function () use ($dto): Sale {
            $saleId = Uuid::uuid4()->toString();
            $soldAt = new DateTimeImmutable();
            $saleItems = [];

            foreach ($dto->items as $itemDto) {
                $quantity = Quantity::fromInt($itemDto->quantity);

                // Obtener el producto activo
                $product = $this->productRepository->findActiveById($itemDto->productId);
                if ($product === null) {
                    throw new EntityNotFoundException("El producto con ID '{$itemDto->productId}' no existe o está dado de baja.");
                }

                // RN-01: Descontar stock (lanza InsufficientStockException si excede)
                $product->withdraw($quantity);
                $this->productRepository->update($product);

                // RN-06: Congelar precio unitario y nombre al momento de la venta
                $saleItems[] = new SaleItem(
                    id: Uuid::uuid4()->toString(),
                    saleId: $saleId,
                    productId: $product->getId(),
                    productName: $product->getName(),
                    unitPrice: $product->getPrice(),
                    quantity: $quantity
                );
            }

            $sale = new Sale(
                id: $saleId,
                userId: $dto->sellerId,
                items: $saleItems,
                createdAt: $soldAt
            );

            $this->saleRepository->save($sale);

            return $sale;
        });
    }
}
