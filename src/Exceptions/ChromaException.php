<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Exceptions;

class ChromaException extends \Exception
{

    public static function create(string $message, string $type, int $code): self
    {
        return match ($type) {
            'NotFoundError' => new NotFoundException($message, $code),
            'ValueError' => new ValueException($message, $code),
            'UniqueConstraintError' => new UniqueConstraintException($message, $code),
            'DimensionalityError' => new DimensionalityException($message, $code),
            'InvalidCollection' => new InvalidCollectionException($message, $code),
            'TypeError' => new TypeException($message, $code),
            'InvalidArgumentError' => new InvalidArgumentException($message, $code),
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
