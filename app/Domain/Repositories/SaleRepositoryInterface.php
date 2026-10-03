<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\Sale;
use App\Domain\ValueObjects\DateRange;

interface SaleRepositoryInterface
{
    public function findById(string $id): ?Sale;

    /**
     * @return array{items: array<Sale>, total: int, page: int, size: int, totalPages: int}
     */
    public function findPaginatedByRange(DateRange $range, int $page, int $size): array;

    public function save(Sale $sale): void;
}
