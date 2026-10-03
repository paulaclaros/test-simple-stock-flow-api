<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Ports\Inbound\GetSales;
use App\Domain\Entities\Sale;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\SaleRepositoryInterface;

/**
 * Caso de Uso: GetSalesService (T-07, E-11, E-12).
 * Consulta listado paginado de ventas y detalle de venta específica.
 */
final class GetSalesService implements GetSales
{
    public function __construct(
        private readonly SaleRepositoryInterface $saleRepository
    ) {
    }

    /**
     * @return array{items: Sale[], total: int}
     */
    public function list(int $page = 1, int $perPage = 15): array
    {
        return $this->saleRepository->paginate($page, $perPage);
    }

    public function getById(string $id): Sale
    {
        $sale = $this->saleRepository->findById($id);
        if ($sale === null) {
            throw new EntityNotFoundException("La venta con ID '{$id}' no fue encontrada.");
        }

        return $sale;
    }
}
