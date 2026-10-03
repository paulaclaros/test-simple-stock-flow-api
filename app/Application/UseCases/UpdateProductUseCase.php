<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\UpdateProductDTO;
use App\Domain\Exceptions\BusinessRuleValidationException;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\ValueObjects\Money;

final class UpdateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {
    }

    public function execute(UpdateProductDTO $dto): void
    {
        $product = $this->productRepository->findActiveById($dto->id);
        if ($product === null) {
            throw new EntityNotFoundException("Producto no encontrado.");
        }

        $category = $this->categoryRepository->findById($dto->categoryId);
        if ($category === null) {
            throw new BusinessRuleValidationException("La categoría indicada no existe.");
        }

        $product->changeName($dto->name);
        $product->changeCategory($dto->categoryId, $category->getName());
        $product->changePrice(Money::fromFloat($dto->price, "COP"));

        $this->productRepository->update($product);
    }
}
