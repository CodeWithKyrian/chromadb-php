<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Requests;

/**
 * Request model for creating a collection.
 */
class CreateCollectionRequest
{
    /**
     * @param string $name The name of the collection
     * @param array<string, string> $metadata The metadata of the collection
     * @param bool $getOrCreate If true, will return existing collection if it exists, otherwise will throw an exception.
     */
    public function __construct(
        public readonly string $name,
        public readonly ?array $metadata,
        public readonly bool $getOrCreate = false,
    ) {}

    public static function create(array $data): self
    {
        return new self(
            name: $data['name'],
            metadata: $data['metadata'] ?? null,
            getOrCreate: $data['get_or_create'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'metadata' => $this->metadata,
            'get_or_create' => $this->getOrCreate,
        ];
    }
}
