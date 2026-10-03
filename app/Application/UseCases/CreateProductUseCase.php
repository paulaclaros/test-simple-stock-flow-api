<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\CreateProductDTO;
use App\Domain\Entities\Product;
use App\Domain\Exceptions\BusinessRuleValidationException;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\ValueObjects\Money;
use Ramsey\Uuid\Uuid;

final class CreateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {
    }

    public function execute(CreateProductDTO $dto): string
    {
        // CA-02.4: Dada una categoría inexistente, la creación se rechaza
        $category = $this->categoryRepository->findById($dto->categoryId);
        if ($category === null) {
            throw new BusinessRuleValidationException("La categoría indicada no existe.");
        }

        $productId = Uuid::uuid4()->toString();
        $money = Money::fromFloat($dto->price, "COP");

        $product = new Product(
            id: $productId,
            name: $dto->name,
            categoryId: $dto->categoryId,
            price: $money,
            stock: $dto->stock,
            categoryName: $category->getName()
        );

        $this->productRepository->save($product);

        return $product->getId();
    }
}
