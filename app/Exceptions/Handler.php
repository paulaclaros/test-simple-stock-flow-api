<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Domain\Exceptions\BusinessRuleValidationException;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Exceptions\InsufficientStockException;
use App\Domain\Exceptions\UnauthorizedException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        BusinessRuleValidationException::class,
        InsufficientStockException::class,
        EntityNotFoundException::class,
        UnauthorizedException::class,
        ForbiddenException::class,
    ];

    public function register(): void
    {
        $this->renderable(function (BusinessRuleValidationException $e) {
            return response()->json([
                'title' => 'Regla de negocio violada',
                'status' => 422,
                'detail' => $e->getMessage(),
            ], 422, ['Content-Type' => 'application/problem+json; charset=utf-8']);
        });

        $this->renderable(function (InsufficientStockException $e) {
            return response()->json([
                'title' => 'Conflicto con otra operación simultánea',
                'status' => 409,
                'detail' => $e->getMessage(),
            ], 409, ['Content-Type' => 'application/problem+json; charset=utf-8']);
        });

        $this->renderable(function (EntityNotFoundException $e) {
            return response()->json([
                'title' => 'No encontrado',
                'status' => 404,
                'detail' => $e->getMessage(),
            ], 404, ['Content-Type' => 'application/problem+json; charset=utf-8']);
        });

        $this->renderable(function (UnauthorizedException $e) {
            return response('', 401)->header('WWW-Authenticate', 'Bearer');
        });

        $this->renderable(function (ForbiddenException $e) {
            // D-C8: 403 vacío
            return response('', 403);
        });
    }
}
