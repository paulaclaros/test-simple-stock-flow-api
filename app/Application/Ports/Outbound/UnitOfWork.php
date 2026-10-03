<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

/**
 * Puerto de Salida: Unidad de Trabajo Transaccional.
 * Definido en ARQUITECTURA-ONION.md (Sección 11 y Artículo II del spec).
 * Ejecuta una operación atómica sin acoplar la capa de aplicación con Laravel o la BD.
 */
interface UnitOfWork
{
    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    public function run(callable $operation): mixed;
}
