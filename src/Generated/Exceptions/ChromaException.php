<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Exceptions;

class ChromaException extends \Exception
{

    public static function throwSpecific(string $message, string $type, int $code)
    {
        switch ($type) {
            case 'NotFoundError':
                throw new ChromaNotFoundException($message, $code);
            case 'ValueError':
                throw new ChromaValueException($message, $code);
            case 'UniqueConstraintError':
                throw new ChromaUniqueConstraintException($message, $code);
            case 'DimensionalityError':
                throw new ChromaDimensionalityException($message, $code);
            case 'TypeError':
                throw new ChromaTypeException($message, $code);
            default:
                throw new self($message, $code);
        }
    }

    public static function inferTypeFromMessage(string $message): string
    {
        return match (true) {
            str_contains($message, 'NotFoundError') => 'NotFoundError',
            str_contains($message, 'UniqueConstraintError') => 'UniqueConstraintError',
            str_contains($message, 'ValueError') => 'ValueError',
            str_contains($message, 'dimensionality') => 'DimensionalityError',
            default => 'UnknownError',
        };
    }
}