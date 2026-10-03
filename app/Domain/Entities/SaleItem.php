<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Exceptions\BusinessRuleValidationException;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\Quantity;

final class SaleItem
{
    private string $id;
    private string $saleId;
    private string $productId;
    private string $productName;
    private string $categoryName;
    private Money $unitPrice;
    private Quantity $quantity;

    public function __construct(
        string $id,
        string $saleId,
        string $productId,
        string $productName,
        string $categoryName,
        Money $unitPrice,
        Quantity $quantity
    ) {
        if (trim($productName) === "") {
            throw new BusinessRuleValidationException("El nombre del producto en la línea de venta no puede estar vacío.");
        }

        if (trim($categoryName) === "") {
            throw new BusinessRuleValidationException("La categoría del producto en la línea de venta no puede estar vacía.");
        }

        if (!$unitPrice->isGreaterThanZero()) {
            throw new BusinessRuleValidationException("El precio unitario de la línea debe ser mayor que cero.");
        }

        $this->id = $id;
        $this->saleId = $saleId;
        $this->productId = $productId;
        $this->productName = trim($productName);
        $this->categoryName = trim($categoryName);
        $this->unitPrice = $unitPrice;
        $this->quantity = $quantity;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSaleId(): string
    {
        return $this->saleId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getCategoryName(): string
    {
        return $this->categoryName;
    }

    public function getUnitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function getQuantity(): Quantity
    {
        return $this->quantity;
    }

    /**
     * Artículo VII: Lo derivado se calcula, nunca se almacena en base de datos.
     */
    public function getSubtotal(): Money
    {
        return $this->unitPrice->times($this->quantity->getValue());
    }
}
