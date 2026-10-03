<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Category;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Mappers\CategoryMapper;
use App\Infrastructure\Persistence\Models\CategoryModel;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findAll(): array
    {
        // D-C1: 200 con un array plano, ordenado por name ascendente
        $models = CategoryModel::query()->orderBy('name', 'asc')->get();

        return $models->map(fn (CategoryModel $m) => CategoryMapper::toDomain($m))->all();
    }

    public function findById(string $id): ?Category
    {
        $model = CategoryModel::query()->find($id);
        if ($model === null) {
            return null;
        }

        return CategoryMapper::toDomain($model);
    }
}
