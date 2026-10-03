<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear esquema 'sales' si no existe
        DB::statement("CREATE SCHEMA IF NOT EXISTS sales;");

        // 2. Tabla sales.category
        DB::statement("
            CREATE TABLE IF NOT EXISTS sales.category (
                id UUID PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE
            );
        ");

        // 3. Tabla sales.user
        DB::statement("
            CREATE TABLE IF NOT EXISTS sales.user (
                id UUID PRIMARY KEY,
                username VARCHAR(255) NOT NULL UNIQUE,
                full_name VARCHAR(255) NOT NULL,
                role VARCHAR(50) NOT NULL CHECK (role IN ('admin', 'seller')),
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 4. Tabla sales.product
        DB::statement("
            CREATE TABLE IF NOT EXISTS sales.product (
                id UUID PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                category_id UUID NOT NULL REFERENCES sales.category(id),
                price NUMERIC(12, 2) NOT NULL CHECK (price > 0),
                stock INTEGER NOT NULL CHECK (stock >= 0),
                image_url VARCHAR(500) NULL,
                deleted_at TIMESTAMP NULL,
                version INTEGER NOT NULL DEFAULT 1
            );
        ");

        // 5. Tabla sales.sale (Sin columna total - Artículo VII)
        DB::statement("
            CREATE TABLE IF NOT EXISTS sales.sale (
                id UUID PRIMARY KEY,
                sold_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                sold_by_user_id UUID NOT NULL REFERENCES sales.user(id),
                sold_by_username VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 6. Tabla sales.sale_item (Sin columna subtotal - Artículo VII)
        DB::statement("
            CREATE TABLE IF NOT EXISTS sales.sale_item (
                id UUID PRIMARY KEY,
                sale_id UUID NOT NULL REFERENCES sales.sale(id) ON DELETE RESTRICT,
                product_id UUID NOT NULL REFERENCES sales.product(id) ON DELETE RESTRICT,
                product_name VARCHAR(255) NOT NULL,
                category_name VARCHAR(255) NOT NULL,
                unit_price NUMERIC(12, 2) NOT NULL CHECK (unit_price > 0),
                quantity INTEGER NOT NULL CHECK (quantity > 0)
            );
        ");

        // 7. Seed inicial de categorías de referencia (CA-08.3)
        $categories = [
            'Electricidad',
            'Fontanería',
            'General',
            'Herramientas',
            'Pinturas',
        ];

        foreach ($categories as $catName) {
            $catId = Uuid::uuid4()->toString();
            DB::statement("
                INSERT INTO sales.category (id, name)
                VALUES (?, ?)
                ON CONFLICT (name) DO NOTHING;
            ", [$catId, $catName]);
        }

        // 8. Seed del administrador inicial de arranque (Bootstrap)
        $adminUsername = strtolower(trim((string) env('ADMIN_USERNAME', 'admin')));
        $adminPassword = (string) env('ADMIN_PASSWORD', 'Admin123*');
        $adminHash = password_hash($adminPassword, PASSWORD_BCRYPT);
        $adminId = Uuid::uuid4()->toString();

        DB::statement("
            INSERT INTO sales.user (id, username, full_name, role, password_hash, created_at)
            VALUES (?, ?, ?, 'admin', ?, CURRENT_TIMESTAMP)
            ON CONFLICT (username) DO NOTHING;
        ", [$adminId, $adminUsername, 'Administrador del Sistema', $adminHash]);
    }

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS sales.sale_item CASCADE;");
        DB::statement("DROP TABLE IF EXISTS sales.sale CASCADE;");
        DB::statement("DROP TABLE IF EXISTS sales.product CASCADE;");
        DB::statement("DROP TABLE IF EXISTS sales.user CASCADE;");
        DB::statement("DROP TABLE IF EXISTS sales.category CASCADE;");
        DB::statement("DROP SCHEMA IF EXISTS sales CASCADE;");
    }
};
