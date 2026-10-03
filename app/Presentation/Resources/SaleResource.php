<?php

declare(strict_types=1);

namespace App\Presentation\Resources;

use App\Domain\Entities\Sale;

final class SaleResource
{
    public static function toArray(Sale $sale): array
    {
        return [
            'id' => $sale->getId(),
            'soldAt' => $sale->getSoldAt()->format('Y-m-d\TH:i:s\Z'),
            'soldByUserId' => $sale->getSoldByUserId(),
            'soldByUsername' => $sale->getSoldByUsername(),
            'items' => SaleItemResource::collection($sale->getItems()),
            'total' => $sale->getTotal()->getAmount(),
        ];
    }

    public static function collection(array $sales): array
    {
        return array_map(fn (Sale $s) => self::toArray($s), $sales);
    }
}
