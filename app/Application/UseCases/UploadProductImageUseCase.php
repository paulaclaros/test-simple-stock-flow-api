<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\Services\ImageStorageServiceInterface;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\ProductRepositoryInterface;

final class UploadProductImageUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ImageStorageServiceInterface $imageStorageService
    ) {
    }

    public function execute(string $productId, mixed $uploadedFile): string
    {
        $product = $this->productRepository->findActiveById($productId);
        if ($product === null) {
            throw new EntityNotFoundException("Producto no encontrado.");
        }

        $imageUrl = $this->imageStorageService->storeImage($uploadedFile);
        $product->setImageUrl($imageUrl);
        $this->productRepository->update($product);

        return $imageUrl;
    }
}
