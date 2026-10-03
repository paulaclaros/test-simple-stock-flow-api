<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Application\DTOs\CreateProductDTO;
use App\Application\DTOs\UpdateProductDTO;
use App\Domain\Entities\Product;

/**
 * Puerto de Entrada: Administrar Productos y Catálogo.
 * Definido en ARQUITECTURA-ONION.md (E-03 a E-08, T-04).
 */
interface ManageProducts
{
    public function create(CreateProductDTO $dto): Product;
    public function update(string $id, UpdateProductDTO $dto): Product;
    public function deactivate(string $id): void;
    public function getById(string $id): Product;
}
