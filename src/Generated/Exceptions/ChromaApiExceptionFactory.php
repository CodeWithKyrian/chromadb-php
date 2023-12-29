<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Exceptions;

class ChromaApiExceptionFactory
{
    public static function make(string $message, string $type,  int $code) : ChromaApiExceptionInterface
    {
        return match ($type) {
            'NotFoundError' => new ChromaNotFoundException($message, $code),
            'ValueError' => new ChromaValueException($message, $code),
            'UniqueConstraintError' => new ChromaUniqueConstraintException($message, $code),
            default => new ChromaApiException($message, $code)
        };
    }
}