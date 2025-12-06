<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Query;

class WhereField
{
    public function __construct(private readonly string $field)
    {
    }

    public function eq(string|int|float|bool $value): array
    {
        return [$this->field => ['$eq' => $value]];
    }

    public function ne(string|int|float|bool $value): array
    {
        return [$this->field => ['$ne' => $value]];
    }

    public function gt(int|float $value): array
    {
        return [$this->field => ['$gt' => $value]];
    }

    public function gte(int|float $value): array
    {
        return [$this->field => ['$gte' => $value]];
    }

    public function lt(int|float $value): array
    {
        return [$this->field => ['$lt' => $value]];
    }

    public function lte(int|float $value): array
    {
        return [$this->field => ['$lte' => $value]];
    }

    public function in(array $values): array
    {
        return [$this->field => ['$in' => $values]];
    }

    public function notIn(array $values): array
    {
        return [$this->field => ['$nin' => $values]];
    }
}
