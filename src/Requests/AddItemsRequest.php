<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Requests;

/**
 * Request model for adding items to collection.
 */
class AddItemsRequest
{
    /**
     * @param float[][] $embeddings Optional embeddings of the items to add.
     * @param array<array<string, string>> $metadatas Optional metadatas of the items to add.
     * @param string[] $ids IDs of the items to add.
     * @param string[] $documents Optional documents of the items to add.
     */
    public function __construct(
        public readonly array $ids,
        public readonly ?array $embeddings = null,
        public readonly ?array $metadatas = null,
        public readonly ?array $documents = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            embeddings: $data['embeddings'] ?? null,
            metadatas: $data['metadatas'] ?? null,
            ids: $data['ids'],
            documents: $data['documents'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'embeddings' => $this->embeddings,
            'metadatas' => $this->metadatas,
            'ids' => $this->ids,
            'documents' => $this->documents,
        ], fn($value) => $value !== null);
    }
}
