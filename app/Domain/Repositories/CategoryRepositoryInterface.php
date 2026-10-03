<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    /**
     * @return array<Category> Ordenadas por nombre ascendente
     */
    public function findAll(): array;

    public function findById(string $id): ?Category;
}
