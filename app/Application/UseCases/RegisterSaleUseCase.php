<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\RegisterSaleDTO;
use App\Application\Services\TransactionManagerInterface;
use App\Domain\Entities\Sale;
use App\Domain\Entities\SaleItem;
use App\Domain\Exceptions\BusinessRuleValidationException;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\ValueObjects\Quantity;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final class RegisterSaleUseCase
{
    public function __construct(
        private readonly SaleRepositoryInterface $saleRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly TransactionManagerInterface $transactionManager
    ) {
    }

    public function execute(RegisterSaleDTO $dto): Sale
    {
        if (empty($dto->items)) {
            throw new BusinessRuleValidationException("La venta debe tener al menos un ítem.");
        }

        // RN-05: Validar que ningún producto se repita en la petición
        $seen = [];
        foreach ($dto->items as $itemDto) {
            if (isset($seen[$itemDto->productId])) {
                throw new BusinessRuleValidationException("Un producto no puede repetirse dentro de la misma venta.");
            }
            $seen[$itemDto->productId] = true;
        }

        return $this->transactionManager->transactional(function () use ($dto): Sale {
            $saleId = Uuid::uuid4()->toString();
            $soldAt = new DateTimeImmutable();
            $saleItems = [];

            foreach ($dto->items as $itemDto) {
                $quantity = Quantity::fromInt($itemDto->quantity);

                // Obtener el producto activo (con bloqueo para concurrencia)
                $product = $this->productRepository->findActiveById($itemDto->productId);
                if ($product === null) {
                    throw new EntityNotFoundException("El producto con ID '{$itemDto->productId}' no existe o está dado de baja.");
                }

                // RN-01: Descontar stock (lanza InsufficientStockException si no alcanza)
                $product->withdraw($quantity);
                $this->productRepository->update($product);

                // RN-06 / CA-04.7: Congelar nombre, categoría y precio en la línea de venta
                $saleItemId = Uuid::uuid4()->toString();
                $saleItems[] = new SaleItem(
                    id: $saleItemId,
                    saleId: $saleId,
                    productId: $product->getId(),
                    productName: $product->getName(),
                    categoryName: $product->getCategoryName() ?? "General",
                    unitPrice: $product->getPrice(),
                    quantity: $quantity
                );
            }

            $sale = new Sale(
                id: $saleId,
                soldAt: $soldAt,
                soldByUserId: $dto->userId,
                soldByUsername: $dto->username,
                items: $saleItems
            );

            $this->saleRepository->save($sale);

            return $sale;
        });
    }
}
