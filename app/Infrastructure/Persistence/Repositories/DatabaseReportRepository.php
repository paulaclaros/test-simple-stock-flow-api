<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Repositories\ReportRepositoryInterface;
use App\Domain\ValueObjects\DateRange;
use Illuminate\Support\Facades\DB;

final class DatabaseReportRepository implements ReportRepositoryInterface
{
    /**
     * CA-06.5: La agregación se resuelve en la base de datos
     */
    public function getSalesReport(DateRange $range): array
    {
        $fromStr = $range->getFrom()->format('Y-m-d H:i:s');
        $toStr = $range->getTo()->format('Y-m-d H:i:s');

        // Contar el número total de ventas distintas en el rango
        $totalSales = (int) DB::table('sales.sale')
            ->where('sold_at', '>=', $fromStr)
            ->where('sold_at', '<', $toStr)
            ->count('id');

        // CA-06.1: Agrupación en base de datos por product_id y category_name congelada
        $rows = DB::table('sales.sale_item as si')
            ->join('sales.sale as s', 'si.sale_id', '=', 's.id')
            ->where('s.sold_at', '>=', $fromStr)
            ->where('s.sold_at', '<', $toStr)
            ->selectRaw('
                si.product_id as product_id,
                si.product_name as product_name,
                si.category_name as category_name,
                SUM(si.quantity) as units_sold,
                SUM(si.quantity * si.unit_price) as total_amount
            ')
            ->groupBy('si.product_id', 'si.product_name', 'si.category_name')
            ->orderBy('si.product_name', 'asc')
            ->get();

        $items = [];
        $grandTotal = 0.0;

        foreach ($rows as $row) {
            $unitsSold = (int) $row->units_sold;
            $amount = round((float) $row->total_amount, 2);
            $grandTotal += $amount;

            $items[] = [
                'productId' => (string) $row->product_id,
                'productName' => (string) $row->product_name,
                'categoryName' => (string) $row->category_name,
                'unitsSold' => $unitsSold,
                'totalAmount' => $amount,
            ];
        }

        return [
            'from' => $range->getFrom()->format('Y-m-d\TH:i:s\Z'),
            'to' => $range->getTo()->format('Y-m-d\TH:i:s\Z'),
            'currency' => 'COP', // D-C10: currency siempre COP
            'totalSales' => $totalSales,
            'grandTotal' => round($grandTotal, 2),
            'items' => $items,
        ];
    }
}
