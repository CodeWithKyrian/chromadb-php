<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Exceptions;

class ChromaException extends \Exception
{

    public static function throwSpecific(string $message, string $type, int $code)
    {
        throw match ($type) {
            'NotFoundError' => new ChromaNotFoundException($message, $code),
            'AuthorizationError' => new ChromaAuthorizationException($message, $code),
            'ValueError' => new ChromaValueException($message, $code),
            'UniqueConstraintError' => new ChromaUniqueConstraintException($message, $code),
            'DimensionalityError' => new ChromaDimensionalityException($message, $code),
            'InvalidCollection' => new ChromaInvalidCollectionException($message, $code),
            'TypeError' => new ChromaTypeException($message, $code),
            'InvalidArgumentError' => new ChromaInvalidArgumentException($message, $code),
            default => new self($message, $code),
        };
    }

    public static function inferTypeFromMessage(string $message): string
    {
        return match (true) {
            str_contains($message, 'NotFoundError') => 'NotFoundError',
            str_contains($message, 'AuthorizationError') => 'AuthorizationError',
            str_contains($message, 'Forbidden') => 'AuthorizationError',
            str_contains($message, 'UniqueConstraintError') => 'UniqueConstraintError',
            str_contains($message, 'ValueError') => 'ValueError',
            str_contains($message, 'dimensionality') => 'DimensionalityError',
            default => 'UnknownError',
        };
    }
}
