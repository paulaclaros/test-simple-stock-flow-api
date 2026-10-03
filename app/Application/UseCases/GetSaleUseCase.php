<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Entities\Sale;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\SaleRepositoryInterface;

final class GetSaleUseCase
{
    public function __construct(
        private readonly SaleRepositoryInterface $saleRepository
    ) {
    }

    public function execute(string $id): Sale
    {
        $sale = $this->saleRepository->findById($id);
        if ($sale === null) {
            throw new EntityNotFoundException("Venta no encontrada.");
        }

        return $sale;
    }
}
