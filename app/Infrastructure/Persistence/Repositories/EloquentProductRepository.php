<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Product;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Mappers\ProductMapper;
use App\Infrastructure\Persistence\Models\ProductModel;

final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findById(string $id): ?Product
    {
        $model = ProductModel::withTrashed()->with('category')->find($id);
        if ($model === null) {
            return null;
        }

        return ProductMapper::toDomain($model);
    }

    public function findActiveById(string $id): ?Product
    {
        $model = ProductModel::query()
            ->with('category')
            ->lockForUpdate()
            ->find($id);

        if ($model === null) {
            return null;
        }

        return ProductMapper::toDomain($model);
    }

    public function findPaginated(int $page, int $size, ?string $search = null, ?string $categoryId = null): array
    {
        $query = ProductModel::query()->with('category');

        if ($search !== null && $search !== '') {
            // CA-01.2: Búsqueda por nombre sin distinguir mayúsculas
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        if ($categoryId !== null && $categoryId !== '') {
            $query->where('category_id', $categoryId);
        }

        $total = $query->count();
        $totalPages = $total > 0 ? (int) ceil($total / $size) : 0;

        $models = $query->orderBy('name', 'asc')
            ->forPage($page, $size)
            ->get();

        $items = $models->map(fn (ProductModel $m) => ProductMapper::toDomain($m))->all();

        return [
            'items' => $items,
            'page' => $page,
            'size' => $size,
            'total' => $total,
            'totalPages' => $totalPages,
        ];
    }

    public function save(Product $product): void
    {
        ProductModel::create([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'category_id' => $product->getCategoryId(),
            'price' => $product->getPrice()->getAmount(),
            'stock' => $product->getStock(),
            'image_url' => $product->getImageUrl(),
            'version' => $product->getVersion(),
        ]);
    }

    public function update(Product $product): void
    {
        $model = ProductModel::withTrashed()->find($product->getId());
        if ($model === null) {
            return;
        }

        $model->name = $product->getName();
        $model->category_id = $product->getCategoryId();
        $model->price = $product->getPrice()->getAmount();
        $model->stock = $product->getStock();
        $model->image_url = $product->getImageUrl();
        $model->version = $product->getVersion();

        if ($product->getDeletedAt() !== null && $model->deleted_at === null) {
            $model->deleted_at = $product->getDeletedAt()->format('Y-m-d H:i:s');
        }

        $model->save();
    }
}
