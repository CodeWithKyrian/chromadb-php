<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Requests;

class UpdateCollectionRequest
{
    /**
     * @param string|null $name New name of the collection.
     * @param array<string,mixed>|null $metadata New metadata of the collection.
     */
    public function __construct(
        public readonly ?string $name,
        public readonly ?array $metadata,
    ) {}

    public static function create(array $data): self
    {
        return new self(
            name: $data['new_name'] ?? null,
            metadata: $data['new_metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'new_name' => $this->name,
            'new_metadata' => $this->metadata,
        ]);
    }
}
