<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Models;

class Collection
{

    public function __construct(
        public readonly string $name,
        public readonly string $id,
        public readonly ?array $metadata,
    )
    {
    }

    public static function make(array $data): self
    {
        return new self(
            name: $data['name'],
            id: $data['id'],
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'id' => $this->id,
            'metadata' => $this->metadata,
        ];
    }
}