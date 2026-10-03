<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\ValueObjects\DateRange;
use DateTimeImmutable;

final class ListSalesUseCase
{
    public function __construct(
        private readonly SaleRepositoryInterface $saleRepository
    ) {
    }

    /**
     * @return array{items: array, total: int, page: int, size: int, totalPages: int}
     */
    public function execute(DateTimeImmutable $from, DateTimeImmutable $to, int $page, int $size): array
    {
        $range = new DateRange($from, $to);
        $normalizedPage = $page < 1 ? 1 : $page;
        $normalizedSize = $size < 1 ? 20 : ($size > 100 ? 100 : $size);

        return $this->saleRepository->findPaginatedByRange($range, $normalizedPage, $normalizedSize);
    }
}
