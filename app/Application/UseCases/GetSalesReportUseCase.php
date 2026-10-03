<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Repositories\ReportRepositoryInterface;
use App\Domain\ValueObjects\DateRange;
use DateTimeImmutable;

final class GetSalesReportUseCase
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepository
    ) {
    }

    /**
     * CA-06.1 - CA-06.5: Agregación resuelta en base de datos
     */
    public function execute(DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $range = new DateRange($from, $to);
        return $this->reportRepository->getSalesReport($range);
    }
}
