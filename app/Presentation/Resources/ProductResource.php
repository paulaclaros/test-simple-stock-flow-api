<?php

declare(strict_types=1);

namespace App\Presentation\Resources;

use App\Domain\Entities\Product;

final class ProductResource
{
    public static function toArray(Product $product): array
    {
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'categoryId' => $product->getCategoryId(),
            'categoryName' => $product->getCategoryName(),
            'price' => $product->getPrice()->getAmount(),
            'stock' => $product->getStock(),
            'imageUrl' => $product->getImageUrl(),
            'isActive' => $product->isActive(),
        ];
    }

    public static function collection(array $products): array
    {
        return array_map(fn (Product $p) => self::toArray($p), $products);
    }
}
