<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Entities\Category;
use App\Domain\Repositories\CategoryRepositoryInterface;

final class ListCategoriesUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {
    }

    /**
     * @return array<Category>
     */
    public function execute(): array
    {
        return $this->categoryRepository->findAll();
    }
}
