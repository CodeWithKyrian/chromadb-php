<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Query;

class WhereDocument
{
    public function contains(string $value): array
    {
        return ['$contains' => $value];
    }

    public function notContains(string $value): array
    {
        return ['$not_contains' => $value];
    }

    public function matches(string $value): array
    {
        return ['$regex' => $value];
    }

    public function notMatches(string $value): array
    {
        return ['$not_regex' => $value];
    }

    public function regex(string $value): array
    {
        return ['$regex' => $value];
    }

    public function notRegex(string $value): array
    {
        return ['$not_regex' => $value];
    }
}
