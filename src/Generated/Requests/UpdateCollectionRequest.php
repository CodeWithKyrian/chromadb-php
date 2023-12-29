<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Requests;

class UpdateCollectionRequest
{
    public function __construct(
        /**
         * New name of the collection.
         */
        public readonly ?string $newName,

        /**
         * New metadata of the collection.
         *
         * @var array<string, string>
         */
        public readonly ?array $newMetadata,

    )
    {
    }

    public static function create(array $data): self
    {
        return new self(
            newName: $data['new_name'] ?? null,
            newMetadata: $data['new_metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'new_name' => $this->newName,
            'new_metadata' => $this->newMetadata,
        ]);
    }
}