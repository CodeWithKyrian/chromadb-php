<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Requests;

class CreateDatabaseRequest
{
    public function __construct(
        public readonly string $name,
    )
    {
    }

    public static function create(array $data): self
    {
        return new self(
            name: $data['name'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}