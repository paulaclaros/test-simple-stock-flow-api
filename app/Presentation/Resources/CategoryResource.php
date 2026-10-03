<?php

declare(strict_types=1);

namespace App\Presentation\Resources;

use App\Domain\Entities\Category;

final class CategoryResource
{
    public static function toArray(Category $category): array
    {
        return [
            'id' => $category->getId(),
            'name' => $category->getName(),
        ];
    }

    public static function collection(array $categories): array
    {
        return array_map(fn (Category $c) => self::toArray($c), $categories);
    }
}
