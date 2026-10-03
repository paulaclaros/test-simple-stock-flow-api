<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Exceptions\BusinessRuleValidationException;
use App\Domain\ValueObjects\Money;
use DateTimeImmutable;

final class Sale
{
    private string $id;
    private DateTimeImmutable $soldAt;
    private string $soldByUserId;
    private string $soldByUsername;
    /** @var array<SaleItem> */
    private array $items;

    /**
     * @param array<SaleItem> $items
     */
    public function __construct(
        string $id,
        DateTimeImmutable $soldAt,
        string $soldByUserId,
        string $soldByUsername,
        array $items
    ) {
        if (empty($items)) {
            throw new BusinessRuleValidationException("La venta debe tener al menos un ítem.");
        }

        // RN-05: Un producto no se repite dentro de una misma venta
        $seenProducts = [];
        foreach ($items as $item) {
            if (!$item instanceof SaleItem) {
                throw new BusinessRuleValidationException("Elemento de venta inválido.");
            }
            if (isset($seenProducts[$item->getProductId()])) {
                throw new BusinessRuleValidationException("Un producto no puede repetirse dentro de la misma venta.");
            }
            $seenProducts[$item->getProductId()] = true;
        }

        $this->id = $id;
        $this->soldAt = $soldAt;
        $this->soldByUserId = $soldByUserId;
        $this->soldByUsername = trim($soldByUsername);
        $this->items = array_values($items);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSoldAt(): DateTimeImmutable
    {
        return $this->soldAt;
    }

    public function getSoldByUserId(): string
    {
        return $this->soldByUserId;
    }

    public function getSoldByUsername(): string
    {
        return $this->soldByUsername;
    }

    /**
     * @return array<SaleItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Artículo VII: Lo derivado se calcula, nunca se almacena en base de datos.
     */
    public function getTotal(): Money
    {
        $total = Money::zero();
        foreach ($this->items as $item) {
            $total = $total->plus($item->getSubtotal());
        }

        return $total;
    }
}
