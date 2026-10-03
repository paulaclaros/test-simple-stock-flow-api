# test-simple-stock-flow-api

> **Prueba Técnica SDD · Ficha ADSO 3413974**  
> **Aprendiz:** Paula Claros  
> **Tecnología:** PHP 8.2+ con Laravel 10 (Arquitectura Hexagonal)  
> **Fecha de entrega:** 2026-10-03 (Horario: 9:00 a. m. a 3:00 p. m.)

---

## 1. Descripción del Repositorio

Este repositorio contiene el **Backend REST** del sistema *Simple Stock Flow*, implementado en **PHP con Laravel** siguiendo una **Arquitectura Hexagonal estricta** (Puertos y Adaptadores).

El servicio aplica las reglas de negocio del inventario y las ventas, expone el contrato REST en estricto formato `camelCase`, emite tokens de autenticación JWT y es el **único dueño del esquema de base de datos** (`sales` en PostgreSQL 16), el cual crea y puebla automáticamente al arrancar mediante migraciones.

---

## 2. Arquitectura Hexagonal Implementada

Siguiendo las directrices del desarrollo guiado por especificación (SDD) y el Artículo I de la Constitución del proyecto (*"El dominio no depende de nada"*), el código dentro de `app/` se organiza en 4 capas desacopladas:

```
app/
├── Domain/                         # 1. EL NÚCLEO (PHP 8.2 Puro, CERO Laravel, CERO Eloquent)
│   ├── Entities/                   # Entidades con identidad e invariantes:
│   │   ├── Product.php             # Control de stock, validación de precio y baja lógica
│   │   ├── Sale.php                # Venta inmutable, cálculo dinámico de total
│   │   ├── SaleItem.php            # Línea de venta con congelamiento de precio y categoría
│   │   ├── User.php                # Usuario del sistema con roles estrictos (admin/seller)
│   │   └── Category.php            # Categorías de clasificación
│   ├── ValueObjects/               # Objetos de valor inmutables:
│   │   ├── Money.php               # Monomoneda COP, redondeo a 2 decimales
│   │   ├── Quantity.php            # Cantidad entera mayor que cero
│   │   └── DateRange.php           # Rango de fechas (from <= sold_at < to)
│   ├── Exceptions/                 # Errores de negocio en español:
│   │   ├── BusinessRuleValidationException.php  -> HTTP 422
│   │   ├── InsufficientStockException.php       -> HTTP 409 (Conflicto / Concurrencia)
│   │   ├── EntityNotFoundException.php          -> HTTP 404
│   │   ├── UnauthorizedException.php            -> HTTP 401
│   │   └── ForbiddenException.php               -> HTTP 403 (Cuerpo vacío)
│   └── Repositories/               # INTERFACES puras (Puertos de salida):
│       ├── ProductRepositoryInterface.php
│       ├── SaleRepositoryInterface.php
│       ├── CategoryRepositoryInterface.php
│       ├── UserRepositoryInterface.php
│       └── ReportRepositoryInterface.php
│
├── Application/                    # 2. CASOS DE USO (Orquestación del negocio)
│   ├── UseCases/                   # 1 acción = 1 clase (Principio SRP):
│   │   ├── LoginUseCase.php                  # Autenticación y generación de JWT
│   │   ├── RegisterUserUseCase.php           # Registro de nuevos usuarios
│   │   ├── ListProductsUseCase.php           # Catálogo paginado con filtros
│   │   ├── GetProductUseCase.php             # Detalle de producto
│   │   ├── CreateProductUseCase.php          # Creación de producto
│   │   ├── UpdateProductUseCase.php          # Edición de producto
│   │   ├── DeactivateProductUseCase.php      # Baja lógica (deleted_at)
│   │   ├── UploadProductImageUseCase.php     # Subida de imagen
│   │   ├── RegisterSaleUseCase.php           # Venta atómica transaccional
│   │   ├── ListSalesUseCase.php              # Consulta de ventas por rango
│   │   ├── GetSaleUseCase.php                # Consulta de venta por ID
│   │   ├── ListCategoriesUseCase.php         # Listado ordenado de categorías
│   │   └── GetSalesReportUseCase.php         # Reporte agregado en motor de BD
│   ├── DTOs/                       # Objetos de transferencia de datos planos
│   └── Services/                   # Interfaces para servicios técnicos:
│       ├── PasswordHasherInterface.php
│       ├── JwtTokenServiceInterface.php
│       ├── ImageStorageServiceInterface.php
│       └── TransactionManagerInterface.php
│
├── Infrastructure/                 # 3. ADAPTADORES TÉCNICOS (Integración con Laravel)
│   ├── Persistence/
│   │   ├── Models/                 # Modelos Eloquent en esquema 'sales':
│   │   │   ├── CategoryModel.php   (sales.category)
│   │   │   ├── UserModel.php       (sales.user)
│   │   │   ├── ProductModel.php    (sales.product con SoftDeletes)
│   │   │   ├── SaleModel.php       (sales.sale - SIN columna total)
│   │   │   └── SaleItemModel.php   (sales.sale_item - SIN columna subtotal)
│   │   ├── Mappers/                # Transforman Modelos Eloquent <--> Entidades Dominio
│   │   └── Repositories/           # Implementación real de los contratos
│   │       ├── EloquentProductRepository.php
│   │       ├── EloquentSaleRepository.php
│   │       ├── EloquentCategoryRepository.php
│   │       ├── EloquentUserRepository.php
│   │       └── DatabaseReportRepository.php (Agrupación directa en SQL)
│   └── Services/                   # Implementaciones técnicas:
│       ├── BcryptPasswordHasherService.php
│       ├── FirebaseJwtTokenService.php
│       ├── LocalStorageImageService.php
│       └── DatabaseTransactionManager.php (DB::transaction)
│
└── Presentation/                   # 4. CAPA EXTERNA (HTTP / API REST)
    ├── Controllers/                # Controladores que reciben peticiones y delegan al UseCase
    ├── Middleware/                 # JwtAuthMiddleware y RoleMiddleware
    └── Resources/                  # Serialización de respuestas en estricto camelCase
```

---

## 3. Matriz de Cumplimiento de Reglas de Negocio (Spec)

| Regla | Descripción | Dónde está implementada en este proyecto |
|---|---|---|
| **RN-01** | El stock de un producto **nunca** es negativo | `Product::withdraw()` valida existencias antes de restar; `CHECK (stock >= 0)` en PostgreSQL; Bloqueo transaccional `lockForUpdate` en `RegisterSaleUseCase`. |
| **RN-02** | El precio de un producto es **mayor que cero** | `Product::__construct()` y `Product::changePrice()` rechazan precios `<= 0`; Restricción `CHECK (price > 0)` en PostgreSQL. |
| **RN-03** | La cantidad vendida es **mayor que cero** | Value Object `Quantity::__construct()` valida `$value > 0`. |
| **RN-04** | Una venta tiene **al menos una** línea | `Sale::__construct()` y `RegisterSaleUseCase` rechazan ventas sin ítems (HTTP 422). |
| **RN-05** | Un producto **no se repite** dentro de una misma venta | `Sale::__construct()` y `RegisterSaleUseCase` validan duplicidad de IDs de producto en las líneas. |
| **RN-06** | Precio, categoría y nombre quedan **congelados** al vender | `SaleItem` copia como snapshots `$productName`, `$categoryName` y `$unitPrice` al momento de la venta y no expone métodos de modificación. |
| **RN-07** | Una venta registrada **no se modifica ni se anula** | La entidad `Sale` es inmutable; no existen métodos `update` ni endpoints `PUT`/`DELETE` en `routes/api.php`. |
| **RN-08** | Un producto vendido **no se elimina**: se da de baja lógica | `Product::deactivate()` establece `deleted_at`; el modelo usa `SoftDeletes`; las ventas históricas conservan sus referencias íntegras. |
| **RN-09** | Monomoneda estricta en COP | `Money::__construct()` valida que la moneda sea siempre `"COP"`. |
| **RN-10** | Nombre de usuario único y en minúsculas | `User::normalizeUsername()` aplica `strtolower(trim($username))`; índice `UNIQUE` en `sales.user(username)`. |
| **RN-11** | Roles restringidos (`admin` o `seller`) | `User::isValidRole()` valida el conjunto cerrado; restricción `CHECK (role IN ('admin', 'seller'))` en la base de datos. |

---

## 4. Contrato de la API REST

Todas las respuestas cumplen las especificaciones de `api-contract.md`:
- Nombres de campos en **`camelCase`**.
- Formato de fecha: ISO 8601 UTC (`2026-10-03T14:30:00Z`).
- Moneda: `"COP"`.
- Autenticación: Cabecera `Authorization: Bearer <token_jwt>`.

### Resumen de Endpoints:

| Método | Ruta | Rol requerido | Descripción |
|---|---|---|---|
| `GET` | `/health` | Anónimo | Comprobación de salud del servicio |
| `POST` | `/api/auth/login` | Anónimo | Inicio de sesión, retorna JWT y datos del usuario |
| `POST` | `/api/auth/register` | `admin` | Alta de nuevo usuario (vendedor o admin) |
| `GET` | `/api/categories` | Autenticado | Listado plano de categorías ordenadas por nombre |
| `GET` | `/api/products` | Autenticado | Catálogo de productos activos paginado con búsqueda y filtro |
| `GET` | `/api/products/{id}` | Autenticado | Detalle de un producto |
| `POST` | `/api/products` | `admin` | Creación de un nuevo producto |
| `PUT` | `/api/products/{id}` | `admin` | Actualización de datos de un producto |
| `DELETE` | `/api/products/{id}` | `admin` | Baja lógica de un producto (`204 No Content`) |
| `POST` | `/api/products/{id}/image` | `admin` | Subida de imagen del producto (Multipart) |
| `POST` | `/api/sales` | Autenticado | Registro transaccional de venta con descuento de stock |
| `GET` | `/api/sales` | Autenticado | Listado paginado de ventas por rango de fechas |
| `GET` | `/api/sales/{id}` | Autenticado | Detalle de una venta con sus líneas y total calculado |
| `GET` | `/api/reports/sales` | Autenticado | Reporte agrupado en base de datos (`from` y `to` requeridos) |

---

## 5. Datos Iniciales y Arranque (Bootstrap)

Al arrancar el contenedor con Docker:
1. `docker-entrypoint.sh` aguarda a que PostgreSQL esté listo.
2. Ejecuta `php artisan migrate --force`.
3. Crea el esquema `sales` y las 5 tablas en singular.
4. Siembra las 5 categorías de referencia:
   - *Electricidad*, *Fontanería*, *General*, *Herramientas*, *Pinturas*.
5. Crea el usuario administrador inicial según variables de entorno:
   - **Usuario:** `admin` (definido en `ADMIN_USERNAME`)
   - **Contraseña:** `Admin123*` (definida en `ADMIN_PASSWORD`)
6. Inicia el servidor web en el puerto `8080`.
