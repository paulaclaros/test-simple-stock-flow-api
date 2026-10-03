<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\ProductRepositoryInterface;

final class DeactivateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function execute(string $id): void
    {
        // RN-08 / CA-02.5: Un producto vendido no se elimina físicamente: se da de baja lógica
        $product = $this->productRepository->findActiveById($id);
        if ($product === null) {
            throw new EntityNotFoundException("Producto no encontrado o ya inactivo.");
        }

        $product->deactivate();
        $this->productRepository->update($product);
    }
}
