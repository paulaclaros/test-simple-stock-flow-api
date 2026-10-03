<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\ValueObjects\DateRange;

interface ReportRepositoryInterface
{
    /**
     * Resuelve la agregación directamente en el motor de base de datos (CA-06.5).
     * Agrupa por producto y etiqueta de categoría congelada (CA-06.1).
     *
     * @return array{
     *     from: string,
     *     to: string,
     *     currency: string,
     *     totalSales: int,
     *     grandTotal: float,
     *     items: array<array{
     *         productId: string,
     *         productName: string,
     *         categoryName: string,
     *         unitsSold: int,
     *         totalAmount: float
     *     }>
     * }
     */
    public function getSalesReport(DateRange $range): array;
}
