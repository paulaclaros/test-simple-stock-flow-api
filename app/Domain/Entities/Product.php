<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Exceptions\BusinessRuleValidationException;
use App\Domain\Exceptions\InsufficientStockException;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\Quantity;
use DateTimeImmutable;

final class Product
{
    private string $id;
    private string $name;
    private string $categoryId;
    private ?string $categoryName;
    private Money $price;
    private int $stock;
    private ?string $imageUrl;
    private ?DateTimeImmutable $deletedAt;
    private int $version;

    public function __construct(
        string $id,
        string $name,
        string $categoryId,
        Money $price,
        int $stock,
        ?string $categoryName = null,
        ?string $imageUrl = null,
        ?DateTimeImmutable $deletedAt = null,
        int $version = 1
    ) {
        $nameTrimmed = trim($name);
        if ($nameTrimmed === "") {
            throw new BusinessRuleValidationException("El nombre del producto es obligatorio.");
        }

        if (trim($categoryId) === "") {
            throw new BusinessRuleValidationException("La categoría es obligatoria.");
        }

        if (!$price->isGreaterThanZero()) {
            throw new BusinessRuleValidationException("El precio del producto debe ser mayor que cero.");
        }

        if ($stock < 0) {
            throw new BusinessRuleValidationException("El stock inicial no puede ser negativo.");
        }

        $this->id = $id;
        $this->name = $nameTrimmed;
        $this->categoryId = $categoryId;
        $this->categoryName = $categoryName;
        $this->price = $price;
        $this->stock = $stock;
        $this->imageUrl = $imageUrl;
        $this->deletedAt = $deletedAt;
        $this->version = $version;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function isActive(): bool
    {
        return $this->deletedAt === null;
    }

    public function withdraw(Quantity $quantity): void
    {
        if (!$this->isActive()) {
            throw new BusinessRuleValidationException("No se puede vender un producto dado de baja.");
        }

        $qty = $quantity->getValue();
        if ($qty > $this->stock) {
            throw new InsufficientStockException("Existencias insuficientes para el producto '{$this->name}'. Solicitado: {$qty}, Disponible: {$this->stock}.");
        }

        $this->stock -= $qty;
        $this->version++;
    }

    public function deactivate(): void
    {
        if (!$this->isActive()) {
            return;
        }

        $this->deletedAt = new DateTimeImmutable();
    }

    public function changePrice(Money $newPrice): void
    {
        if (!$newPrice->isGreaterThanZero()) {
            throw new BusinessRuleValidationException("El precio del producto debe ser mayor que cero.");
        }

        $this->price = $newPrice;
    }

    public function changeName(string $newName): void
    {
        $trimmed = trim($newName);
        if ($trimmed === "") {
            throw new BusinessRuleValidationException("El nombre del producto no puede estar vacío.");
        }

        $this->name = $trimmed;
    }

    public function changeCategory(string $categoryId, ?string $categoryName = null): void
    {
        if (trim($categoryId) === "") {
            throw new BusinessRuleValidationException("La categoría no puede estar vacía.");
        }

        $this->categoryId = $categoryId;
        $this->categoryName = $categoryName;
    }

    public function setImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }
}
