<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Query;

class Where
{
    /**
     * Start a WHERE clause for a specific metadata field.
     */
    public static function field(string $field): WhereField
    {
        return new WhereField($field);
    }

    /**
     * Start a WHERE clause for document content.
     */
    public static function document(): WhereDocument
    {
        return new WhereDocument();
    }

    /**
     * Combine multiple conditions with logical AND.
     */
    public static function all(array ...$conditions): array
    {
        return ['$and' => $conditions];
    }

    /**
     * Combine multiple conditions with logical OR.
     */
    public static function any(array ...$conditions): array
    {
        return ['$or' => $conditions];
    }
}
