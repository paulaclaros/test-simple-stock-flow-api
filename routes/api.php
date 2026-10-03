<?php

declare(strict_types=1);

use App\Presentation\Controllers\AuthController;
use App\Presentation\Controllers\CategoryController;
use App\Presentation\Controllers\HealthController;
use App\Presentation\Controllers\ProductController;
use App\Presentation\Controllers\ReportController;
use App\Presentation\Controllers\SaleController;
use App\Presentation\Middleware\JwtAuthMiddleware;
use App\Presentation\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

// Salud del servicio
Route::get('/health', [HealthController::class, 'health']);

// Autenticación pública
Route::post('/api/auth/login', [AuthController::class, 'login']);

// Catálogo y Categorías (Lectura autenticada o pública)
Route::get('/api/categories', [CategoryController::class, 'index']);
Route::get('/api/products', [ProductController::class, 'index']);
Route::get('/api/products/{id}', [ProductController::class, 'show']);

// Rutas protegidas para vendedores y administradores
Route::middleware([JwtAuthMiddleware::class])->group(function () {
    Route::post('/api/sales', [SaleController::class, 'store']);
    Route::get('/api/sales', [SaleController::class, 'index']);
    Route::get('/api/sales/{id}', [SaleController::class, 'show']);
    Route::get('/api/reports/sales', [ReportController::class, 'salesReport']);
});

// Rutas exclusivas para administradores
Route::middleware([JwtAuthMiddleware::class, RoleMiddleware::class . ':admin'])->group(function () {
    Route::post('/api/auth/register', [AuthController::class, 'register']);
    Route::post('/api/products', [ProductController::class, 'store']);
    Route::put('/api/products/{id}', [ProductController::class, 'update']);
    Route::delete('/api/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/api/products/{id}/image', [ProductController::class, 'uploadImage']);
});

// Servir archivos multimedia subidos
Route::get('/media/{filename}', function (string $filename) {
    $path = storage_path('app/public/media/' . $filename);
    if (!file_exists($path)) {
        return response('', 404);
    }
    return response()->file($path);
});
