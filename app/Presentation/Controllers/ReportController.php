<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\GetSalesReportUseCase;
use DateTimeImmutable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReportController
{
    public function __construct(
        private readonly GetSalesReportUseCase $getSalesReportUseCase
    ) {
    }

    public function salesReport(Request $request): JsonResponse
    {
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

        $report = $this->getSalesReportUseCase->execute($from, $to);

        return response()->json($report, 200);
    }
}
