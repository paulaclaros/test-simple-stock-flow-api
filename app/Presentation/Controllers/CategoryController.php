<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\ListCategoriesUseCase;
use App\Presentation\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;

final class CategoryController
{
    public function __construct(
        private readonly ListCategoriesUseCase $listCategoriesUseCase
    ) {
    }

    public function index(): JsonResponse
    {
        // D-C1: 200 OK con un array plano ordenado por nombre, vacío es []
        $categories = $this->listCategoriesUseCase->execute();

        return response()->json(CategoryResource::collection($categories), 200);
    }
}
