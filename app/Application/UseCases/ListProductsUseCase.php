<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Repositories\ProductRepositoryInterface;

final class ListProductsUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * @return array{items: array, total: int, page: int, size: int, totalPages: int}
     */
    public function execute(int $page, int $size, ?string $search = null, ?string $categoryId = null): array
    {
        // D-C5: Normalización de paginación según contrato
        $normalizedPage = $page < 1 ? 1 : $page;
        $normalizedSize = $size < 1 ? 20 : ($size > 100 ? 100 : $size);

        return $this->productRepository->findPaginated(
            page: $normalizedPage,
            size: $normalizedSize,
            search: $search !== null ? trim($search) : null,
            categoryId: $categoryId !== null ? trim($categoryId) : null
        );
    }
}
