<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Domain\ValueObjects\DateRange;

/**
 * Puerto de Entrada: Reporte de Ventas (GetSalesReport).
 * Definido en ARQUITECTURA-ONION.md (E-13, T-08).
 */
interface GetSalesReport
{
    /**
     * @return array{rows: array<int, array{productId: string, productName: string, unitsSold: int, revenue: float}>, totalUnits: int, totalRevenue: float, currency: string}
     */
    public function generate(DateRange $range): array;
}
