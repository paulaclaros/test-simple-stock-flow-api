<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Ports\Outbound\UnitOfWork;
use Illuminate\Support\Facades\DB;

/**
 * Adaptador de Infraestructura: LaravelUnitOfWork.
 * Implementa el puerto UnitOfWork utilizando DB::transaction().
 * R-04: Esta clase es la única autorizada a contener DB::transaction().
 */
final class LaravelUnitOfWork implements UnitOfWork
{
    public function run(callable $operation): mixed
    {
        return DB::transaction($operation);
    }
}
