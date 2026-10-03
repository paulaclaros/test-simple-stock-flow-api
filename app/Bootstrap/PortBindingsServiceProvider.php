<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Application\Ports\Inbound\Authenticate;
use App\Application\Ports\Inbound\GetSales;
use App\Application\Ports\Inbound\GetSalesReport;
use App\Application\Ports\Inbound\ManageProducts;
use App\Application\Ports\Inbound\PlaceSale;
use App\Application\Ports\Outbound\UnitOfWork;
use App\Application\Services\ImageStorageServiceInterface;
use App\Application\Services\JwtTokenServiceInterface;
use App\Application\Services\PasswordHasherInterface;
use App\Application\Services\TransactionManagerInterface;
use App\Application\UseCase\AuthenticationService;
use App\Application\UseCase\GetSalesService;
use App\Application\UseCase\GetSalesReportService;
use App\Application\UseCase\ProductCatalogService;
use App\Application\UseCase\PlaceSaleService;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\ReportRepositoryInterface;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\LaravelUnitOfWork;
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
 * Anillo de Ensamblaje (Bootstrap):
 * Conecta las interfaces y puertos de la Arquitectura Cebolla (Onion)
 * con sus implementaciones concretas de Infraestructura y Aplicación.
 * R-06: Solo app/Bootstrap/ y este proveedor instancian o enlazan Infrastructure.
 */
class PortBindingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1. Puertos de Entrada (Inbound Ports -> Use Cases)
        $this->app->bind(PlaceSale::class, PlaceSaleService::class);
        $this->app->bind(ManageProducts::class, ProductCatalogService::class);
        $this->app->bind(GetSales::class, GetSalesService::class);
        $this->app->bind(GetSalesReport::class, SalesReportService::class);
        $this->app->bind(Authenticate::class, AuthenticationService::class);

        // 2. Puertos de Salida: Repositorios (Outbound Ports)
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(SaleRepositoryInterface::class, EloquentSaleRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(ReportRepositoryInterface::class, DatabaseReportRepository::class);

        // 3. Transaccionalidad (UnitOfWork Port)
        $this->app->bind(UnitOfWork::class, LaravelUnitOfWork::class);
        $this->app->bind(TransactionManagerInterface::class, DatabaseTransactionManager::class);

        // 4. Seguridad y Criptografía
        $this->app->bind(PasswordHasherInterface::class, BcryptPasswordHasherService::class);
        $this->app->bind(JwtTokenServiceInterface::class, FirebaseJwtTokenService::class);

        // 5. Almacenamiento de Medios (File Storage Port)
        $this->app->bind(ImageStorageServiceInterface::class, LocalStorageImageService::class);
    }

    public function boot(): void
    {
    }
}
