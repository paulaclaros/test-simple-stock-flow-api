<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class RegisterSaleDTO
{
    /**
     * @param array<RegisterSaleItemDTO> $items
     */
    public function __construct(
        public readonly string $userId,
        public readonly string $username,
        public readonly array $items
    ) {
    }
}
