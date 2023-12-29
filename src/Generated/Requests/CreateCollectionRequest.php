<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Requests;

/**
 * Request model for creating a collection.
 */
class CreateCollectionRequest
{
    public function __construct(
        /**
         * The name of the collection
         */
        public readonly string $name,

        /**
         * The metadata of the collection
         *
         * @var array<string, string>
         */
        public readonly ?array $metadata,

        /**
         * If true, will return existing collection if it exists.
         */
        public readonly bool $getOrCreate = false,
    )
    {
    }

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