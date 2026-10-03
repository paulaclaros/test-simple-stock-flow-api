<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Exceptions\BusinessRuleValidationException;

final class Category
{
    private string $id;
    private string $name;

    public function __construct(string $id, string $name)
    {
        $nameTrimmed = trim($name);
        if ($nameTrimmed === "") {
            throw new BusinessRuleValidationException("El nombre de la categoría es obligatorio.");
        }

        $this->id = $id;
        $this->name = $nameTrimmed;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
