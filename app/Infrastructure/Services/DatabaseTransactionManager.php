<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\Services\TransactionManagerInterface;
use Illuminate\Support\Facades\DB;

final class DatabaseTransactionManager implements TransactionManagerInterface
{
    public function transactional(callable $operation): mixed
    {
        return DB::transaction($operation);
    }
}
