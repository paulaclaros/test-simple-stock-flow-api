<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\RegisterSaleDTO;
use App\Application\DTOs\RegisterSaleItemDTO;
use App\Application\UseCases\GetSaleUseCase;
use App\Application\UseCases\ListSalesUseCase;
use App\Application\UseCases\RegisterSaleUseCase;
use App\Presentation\Resources\SaleResource;
use DateTimeImmutable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SaleController
{
    public function __construct(
        private readonly RegisterSaleUseCase $registerSaleUseCase,
        private readonly ListSalesUseCase $listSalesUseCase,
        private readonly GetSaleUseCase $getSaleUseCase
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $authUser = $request->attributes->get('auth_user', []);
        $userId = (string) ($authUser['sub'] ?? '00000000-0000-0000-0000-000000000000');
        $username = (string) ($authUser['username'] ?? 'seller');

        $rawItems = $request->input('items', []);
        if (!is_array($rawItems) || empty($rawItems)) {
            return response()->json([
                'title' => 'Venta inválida',
                'status' => 422,
                'detail' => 'La venta debe tener al menos un ítem.',
            ], 422, ['Content-Type' => 'application/problem+json']);
        }

        $items = [];
        foreach ($rawItems as $rawItem) {
            $productId = (string) ($rawItem['productId'] ?? $rawItem['product_id'] ?? '');
            $quantity = (int) ($rawItem['quantity'] ?? 0);
            $items[] = new RegisterSaleItemDTO($productId, $quantity);
        }

        $dto = new RegisterSaleDTO(
            userId: $userId,
            username: $username,
            items: $items
        );

        $sale = $this->registerSaleUseCase->execute($dto);

        return response()->json(SaleResource::toArray($sale), 201);
    }

    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $size = (int) $request->query('size', 20);
        $fromStr = (string) $request->query('from', '');
        $toStr = (string) $request->query('to', '');

        // D-C4: from y to son obligatorios de verdad
        if ($fromStr === '' || $toStr === '') {
            return response()->json([
                'title' => 'Parámetros requeridos faltantes',
                'status' => 400,
                'detail' => 'Los parámetros from y to son obligatorios.',
            ], 400, ['Content-Type' => 'application/problem+json']);
        }

        try {
            $from = new DateTimeImmutable($fromStr);
            $to = new DateTimeImmutable($toStr);
        } catch (Exception) {
            return response()->json([
                'title' => 'Formato de fecha inválido',
                'status' => 400,
                'detail' => 'Las fechas deben enviarse en formato ISO 8601 con zona horaria explícita.',
            ], 400, ['Content-Type' => 'application/problem+json']);
        }

        $result = $this->listSalesUseCase->execute($from, $to, $page, $size);

        return response()->json([
            'items' => SaleResource::collection($result['items']),
            'page' => $result['page'],
            'size' => $result['size'],
            'total' => $result['total'],
            'totalPages' => $result['totalPages'],
        ], 200);
    }

    public function show(string $id): JsonResponse
    {
        $sale = $this->getSaleUseCase->execute($id);

        return response()->json(SaleResource::toArray($sale), 200);
    }
}
