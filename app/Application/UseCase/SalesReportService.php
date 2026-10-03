<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Ports\Inbound\GetSalesReport;
use App\Domain\Repositories\ReportRepositoryInterface;
use App\Domain\ValueObjects\DateRange;

/**
 * Caso de Uso: SalesReportService (T-08, E-13).
 * Genera el reporte consolidado por producto en un rango de fechas.
 */
final class SalesReportService implements GetSalesReport
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepository
    ) {
    }

    /**
     * @return array{rows: array<int, array{productId: string, productName: string, unitsSold: int, revenue: float}>, totalUnits: int, totalRevenue: float, currency: string}
     */
    public function generate(DateRange $range): array
    {
        return $this->reportRepository->getSalesReport($range);
    }
}
