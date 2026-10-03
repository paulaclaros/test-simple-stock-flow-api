<?php

declare(strict_types=1);

namespace App\Presentation\Resources;

use App\Domain\Entities\SaleItem;

final class SaleItemResource
{
    public static function toArray(SaleItem $item): array
    {
        return [
            'id' => $item->getId(),
            'productId' => $item->getProductId(),
            'productName' => $item->getProductName(),
            'categoryName' => $item->getCategoryName(),
            'quantity' => $item->getQuantity()->getValue(),
            'unitPrice' => $item->getUnitPrice()->getAmount(),
            'subtotal' => $item->getSubtotal()->getAmount(),
        ];
    }

    public static function collection(array $items): array
    {
        return array_map(fn (SaleItem $i) => self::toArray($i), $items);
    }
}
