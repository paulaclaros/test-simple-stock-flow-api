<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Domain\Entities\Sale;

/**
 * Puerto de Entrada: Consultar Ventas (GetSales).
 * Definido en ARQUITECTURA-ONION.md (E-11, E-12, T-07).
 */
interface GetSales
{
    /**
     * @return array{items: Sale[], total: int}
     */
    public function list(int $page = 1, int $perPage = 15): array;
    public function getById(string $id): Sale;
}
