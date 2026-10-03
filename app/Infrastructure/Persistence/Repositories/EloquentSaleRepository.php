<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Sale;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\ValueObjects\DateRange;
use App\Infrastructure\Persistence\Mappers\SaleMapper;
use App\Infrastructure\Persistence\Models\SaleItemModel;
use App\Infrastructure\Persistence\Models\SaleModel;

final class EloquentSaleRepository implements SaleRepositoryInterface
{
    public function findById(string $id): ?Sale
    {
        $model = SaleModel::query()->with('items')->find($id);
        if ($model === null) {
            return null;
        }

        return SaleMapper::toDomain($model);
    }

    public function findPaginatedByRange(DateRange $range, int $page, int $size): array
    {
        // D-C2: from inclusivo, to exclusivo (from <= sold_at < to)
        $fromStr = $range->getFrom()->format('Y-m-d H:i:s');
        $toStr = $range->getTo()->format('Y-m-d H:i:s');

        $query = SaleModel::query()
            ->with('items')
            ->where('sold_at', '>=', $fromStr)
            ->where('sold_at', '<', $toStr);

        $total = $query->count();
        $totalPages = $total > 0 ? (int) ceil($total / $size) : 0;

        $models = $query->orderBy('sold_at', 'desc')
            ->forPage($page, $size)
            ->get();

        $items = $models->map(fn (SaleModel $m) => SaleMapper::toDomain($m))->all();

        return [
            'items' => $items,
            'page' => $page,
            'size' => $size,
            'total' => $total,
            'totalPages' => $totalPages,
        ];
    }

    public function save(Sale $sale): void
    {
        // Artículo VII: Lo derivado se calcula, nunca se almacena (ni total ni subtotal)
        SaleModel::create([
            'id' => $sale->getId(),
            'sold_at' => $sale->getSoldAt()->format('Y-m-d H:i:s'),
            'sold_by_user_id' => $sale->getSoldByUserId(),
            'sold_by_username' => $sale->getSoldByUsername(),
            'created_at' => $sale->getSoldAt()->format('Y-m-d H:i:s'),
        ]);

        foreach ($sale->getItems() as $item) {
            SaleItemModel::create([
                'id' => $item->getId(),
                'sale_id' => $item->getSaleId(),
                'product_id' => $item->getProductId(),
                'product_name' => $item->getProductName(),
                'category_name' => $item->getCategoryName(),
                'unit_price' => $item->getUnitPrice()->getAmount(),
                'quantity' => $item->getQuantity()->getValue(),
            ]);
        }
    }
}
