#!/usr/bin/env bash
set -e

echo "=== 1. Validando Composer ==="
composer validate --no-check-publish

echo "=== 2. Verificando Regla R-01: Dominio puro (cero Illuminate) ==="
if grep -R "Illuminate\\\\" app/Domain; then
    echo "ERROR: Se encontraron dependencias de Laravel en app/Domain"
    exit 1
fi
echo "OK: app/Domain es 100% PHP puro."

echo "=== 3. Verificando Regla Onion: Aplicación no usa DB:: directamente ==="
if grep -R "DB::" app/Application; then
    echo "ERROR: Se encontró uso directo de DB:: en app/Application. Debe usarse UnitOfWork."
    exit 1
fi
echo "OK: app/Application delega la transaccionalidad mediante UnitOfWork."

echo "=== 4. Ejecutando pruebas unitarias y de arquitectura (PHPUnit) ==="
./vendor/bin/phpunit --testsuite=Domain,Application,Architecture

echo "============================================="
echo "   ¡VERIFICACIÓN DE ARQUITECTURA EXITOSA!    "
echo "============================================="
