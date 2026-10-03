<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Services\ImageStorageServiceInterface;
use App\Application\Services\JwtTokenServiceInterface;
use App\Application\Services\PasswordHasherInterface;
use App\Application\Services\TransactionManagerInterface;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\ReportRepositoryInterface;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\DatabaseReportRepository;
use App\Infrastructure\Persistence\Repositories\EloquentCategoryRepository;
use App\Infrastructure\Persistence\Repositories\EloquentProductRepository;
use App\Infrastructure\Persistence\Repositories\EloquentSaleRepository;
use App\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use App\Infrastructure\Services\BcryptPasswordHasherService;
use App\Infrastructure\Services\DatabaseTransactionManager;
use App\Infrastructure\Services\FirebaseJwtTokenService;
use App\Infrastructure\Services\LocalStorageImageService;
use Illuminate\Support\ServiceProvider;

/**
 * Artículo III de la Constitución: Un único punto de composición.
 * Aquí los puertos de Dominio y Aplicación se encuentran con sus adaptadores.
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositorios
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(SaleRepositoryInterface::class, EloquentSaleRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(ReportRepositoryInterface::class, DatabaseReportRepository::class);

        // Servicios técnicos
        $this->app->bind(PasswordHasherInterface::class, BcryptPasswordHasherService::class);
        $this->app->bind(JwtTokenServiceInterface::class, FirebaseJwtTokenService::class);
        $this->app->bind(ImageStorageServiceInterface::class, LocalStorageImageService::class);
        $this->app->bind(TransactionManagerInterface::class, DatabaseTransactionManager::class);
    }

    public function boot(): void
    {
    }
}
