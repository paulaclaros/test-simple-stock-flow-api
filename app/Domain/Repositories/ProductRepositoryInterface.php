<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\Product;

interface ProductRepositoryInterface
{
    public function findById(string $id): ?Product;

    public function findActiveById(string $id): ?Product;

    /**
     * @return array{items: array<Product>, total: int, page: int, size: int, totalPages: int}
     */
    public function findPaginated(int $page, int $size, ?string $search = null, ?string $categoryId = null): array;

    public function save(Product $product): void;

    public function update(Product $product): void;
}
