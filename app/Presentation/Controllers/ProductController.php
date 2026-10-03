<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\CreateProductDTO;
use App\Application\DTOs\UpdateProductDTO;
use App\Application\UseCases\CreateProductUseCase;
use App\Application\UseCases\DeactivateProductUseCase;
use App\Application\UseCases\GetProductUseCase;
use App\Application\UseCases\ListProductsUseCase;
use App\Application\UseCases\UpdateProductUseCase;
use App\Application\UseCases\UploadProductImageUseCase;
use App\Presentation\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ProductController
{
    public function __construct(
        private readonly ListProductsUseCase $listProductsUseCase,
        private readonly GetProductUseCase $getProductUseCase,
        private readonly CreateProductUseCase $createProductUseCase,
        private readonly UpdateProductUseCase $updateProductUseCase,
        private readonly DeactivateProductUseCase $deactivateProductUseCase,
        private readonly UploadProductImageUseCase $uploadProductImageUseCase
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $size = (int) $request->query('size', 20);
        $search = $request->query('search');
        $categoryId = $request->query('categoryId');

        $result = $this->listProductsUseCase->execute(
            page: $page,
            size: $size,
            search: is_string($search) ? $search : null,
            categoryId: is_string($categoryId) ? $categoryId : null
        );

        return response()->json([
            'items' => ProductResource::collection($result['items']),
            'page' => $result['page'],
            'size' => $result['size'],
            'total' => $result['total'],
            'totalPages' => $result['totalPages'],
        ], 200);
    }

    public function show(string $id): JsonResponse
    {
        $product = $this->getProductUseCase->execute($id);

        return response()->json(ProductResource::toArray($product), 200);
    }

    public function store(Request $request): JsonResponse
    {
        $name = (string) $request->input('name', '');
        $categoryId = (string) $request->input('categoryId', '');
        $price = (float) $request->input('price', 0.0);
        $stock = (int) $request->input('stock', 0);

        $dto = new CreateProductDTO(
            name: $name,
            categoryId: $categoryId,
            price: $price,
            stock: $stock
        );

        $id = $this->createProductUseCase->execute($dto);

        return response()->json(['id' => $id], 201);
    }

    public function update(Request $request, string $id): Response
    {
        $name = (string) $request->input('name', '');
        $categoryId = (string) $request->input('categoryId', '');
        $price = (float) $request->input('price', 0.0);

        $dto = new UpdateProductDTO(
            id: $id,
            name: $name,
            categoryId: $categoryId,
            price: $price
        );

        $this->updateProductUseCase->execute($dto);

        return response()->noContent();
    }

    public function destroy(string $id): Response
    {
        // RN-08: Baja lógica
        $this->deactivateProductUseCase->execute($id);

        return response()->noContent();
    }

    public function uploadImage(Request $request, string $id): JsonResponse
    {
        $file = $request->file('image') ?: $request->file('file');
        if (!$file) {
            return response()->json([
                'title' => 'Archivo no enviado',
                'status' => 400,
                'detail' => 'Debe enviar un archivo de imagen en el campo image o file.',
            ], 400, ['Content-Type' => 'application/problem+json']);
        }

        $imageUrl = $this->uploadProductImageUseCase->execute($id, $file);

        return response()->json(['imageUrl' => $imageUrl], 200);
    }
}
