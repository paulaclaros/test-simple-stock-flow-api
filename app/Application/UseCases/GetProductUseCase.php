<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Entities\Product;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\ProductRepositoryInterface;

final class GetProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function execute(string $id): Product
    {
        $product = $this->productRepository->findActiveById($id);
        if ($product === null) {
            throw new EntityNotFoundException("Producto no encontrado.");
        }

        return $product;
    }
}
