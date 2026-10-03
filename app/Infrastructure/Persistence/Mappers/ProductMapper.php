<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\Product;
use App\Domain\ValueObjects\Money;
use App\Infrastructure\Persistence\Models\ProductModel;
use DateTimeImmutable;

final class ProductMapper
{
    public static function toDomain(ProductModel $model): Product
    {
        return new Product(
            id: (string) $model->id,
            name: (string) $model->name,
            categoryId: (string) $model->category_id,
            price: Money::fromFloat((float) $model->price, "COP"),
            stock: (int) $model->stock,
            categoryName: $model->category ? (string) $model->category->name : null,
            imageUrl: $model->image_url ? (string) $model->image_url : null,
            deletedAt: $model->deleted_at ? DateTimeImmutable::createFromMutable($model->deleted_at) : null,
            version: (int) ($model->version ?? 1)
        );
    }
}
