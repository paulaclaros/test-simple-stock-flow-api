<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Application\DTOs\RegisterSaleDTO;
use App\Domain\Entities\Sale;

/**
 * Puerto de Entrada: Registrar Venta (PlaceSale).
 * Definido en ARQUITECTURA-ONION.md (E-10, T-10).
 */
interface PlaceSale
{
    public function execute(RegisterSaleDTO $dto): Sale;
}
