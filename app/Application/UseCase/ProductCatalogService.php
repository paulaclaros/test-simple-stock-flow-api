<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTOs\CreateProductDTO;
use App\Application\DTOs\UpdateProductDTO;
use App\Application\Ports\Inbound\ManageProducts;
use App\Application\Services\ImageStorageServiceInterface;
use App\Domain\Entities\Product;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\Quantity;
use Ramsey\Uuid\Uuid;

/**
 * Caso de Uso: ProductCatalogService (T-04, E-03 a E-08).
 * Administra el catálogo de productos: listado, creación, edición, baja lógica e imagen.
 */
final class ProductCatalogService implements ManageProducts
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly ?ImageStorageServiceInterface $imageStorage = null
    ) {
    }

    public function create(CreateProductDTO $dto): Product
    {
        $category = $this->categoryRepository->findById($dto->categoryId);
        if ($category === null) {
            throw new EntityNotFoundException("La categoría '{$dto->categoryId}' no existe.");
        }

        $product = new Product(
            id: Uuid::uuid4()->toString(),
            name: $dto->name,
            categoryId: $dto->categoryId,
            price: Money::fromDecimal($dto->price),
            stock: Quantity::fromInt($dto->stock),
            imageUrl: null,
            isActive: true
        );

        $this->productRepository->save($product);

        return $product;
    }

    public function update(string $id, UpdateProductDTO $dto): Product
    {
        $product = $this->productRepository->findActiveById($id);
        if ($product === null) {
            throw new EntityNotFoundException("El producto no existe o está dado de baja.");
        }

        $category = $this->categoryRepository->findById($dto->categoryId);
        if ($category === null) {
            throw new EntityNotFoundException("La categoría '{$dto->categoryId}' no existe.");
        }

        $product->update(
            name: $dto->name,
            categoryId: $dto->categoryId,
            price: Money::fromDecimal($dto->price),
            stock: Quantity::fromInt($dto->stock)
        );

        $this->productRepository->update($product);

        return $product;
    }

    public function deactivate(string $id): void
    {
        $product = $this->productRepository->findActiveById($id);
        if ($product === null) {
            throw new EntityNotFoundException("El producto no existe o ya está dado de baja.");
        }

        $product->deactivate();
        $this->productRepository->update($product);
    }

    public function getById(string $id): Product
    {
        $product = $this->productRepository->findActiveById($id);
        if ($product === null) {
            throw new EntityNotFoundException("El producto con ID '{$id}' no fue encontrado.");
        }

        return $product;
    }
}
