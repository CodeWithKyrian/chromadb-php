<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Requests;

/**
 * Request model for forking a collection.
 */
class ForkCollectionRequest
{
    /**
     * @param string $newName The name for the forked collection
     */
    public function __construct(
        public readonly string $newName,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            newName: $data['new_name'],
        );
    }

    public function toArray(): array
    {
        return [
            'new_name' => $this->newName,
        ];
    }
}
