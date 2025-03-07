<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Exceptions;

class ValidationException extends \Exception
{
    public function __construct(
        public readonly array  $loc,
        string                 $message,
        public readonly string $type,
        int                    $code = 422,
        \Throwable|null        $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function make(array $data): self
    {
        return new self(
            loc: $data['loc'],
            message: $data['msg'],
            type: $data['type'],
        );
    }

    public function __toString(): string
    {
        return "ValidationException: {$this->message}";
    }

    public function toArray(): array
    {
        return [
            'loc' => $this->loc,
            'message' => $this->message,
            'type' => $this->type,
        ];
    }

}
