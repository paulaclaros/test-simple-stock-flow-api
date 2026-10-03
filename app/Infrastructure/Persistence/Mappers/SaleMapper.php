<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\Sale;
use App\Domain\Entities\SaleItem;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\Quantity;
use App\Infrastructure\Persistence\Models\SaleItemModel;
use App\Infrastructure\Persistence\Models\SaleModel;
use DateTimeImmutable;

final class SaleMapper
{
    public static function toDomain(SaleModel $model): Sale
    {
        $domainItems = [];
        if ($model->relationLoaded('items') || $model->items) {
            foreach ($model->items as $itemModel) {
                $domainItems[] = self::itemToDomain($itemModel);
            }
        }

        return new Sale(
            id: (string) $model->id,
            soldAt: DateTimeImmutable::createFromMutable($model->sold_at),
            soldByUserId: (string) $model->sold_by_user_id,
            soldByUsername: (string) $model->sold_by_username,
            items: $domainItems
        );
    }

    public static function itemToDomain(SaleItemModel $model): SaleItem
    {
        return new SaleItem(
            id: (string) $model->id,
            saleId: (string) $model->sale_id,
            productId: (string) $model->product_id,
            productName: (string) $model->product_name,
            categoryName: (string) $model->category_name,
            unitPrice: Money::fromFloat((float) $model->unit_price, "COP"),
            quantity: Quantity::fromInt((int) $model->quantity)
        );
    }
}
