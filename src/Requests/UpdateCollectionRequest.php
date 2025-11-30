<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Requests;

class UpdateCollectionRequest
{
    /**
     * @param mixed $newName New name of the collection.
     * @param mixed $newMetadata New metadata of the collection.
     */
    public function __construct(
        public readonly ?string $newName,
        public readonly ?array $newMetadata,
    ) {}

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
