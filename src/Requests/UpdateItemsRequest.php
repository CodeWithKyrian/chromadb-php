<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Requests;

class UpdateItemsRequest
{
    /**
     * @param float[][] $embeddings Optional embeddings of the items to update.
     * @param string[] $ids IDs of the items to update.
     * @param array<string, string> $metadatas Optional metadatas of the items to update.
     * @param string[] $documents Optional documents of the items to update.
     * @param string[] $images Optional images of the items to update.
     */
    public function __construct(
        public readonly ?array $embeddings,
        public readonly array  $ids,
        public readonly ?array $metadatas,
        public readonly ?array $documents,
        public readonly ?array $images,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            embeddings: $data['embeddings'] ?? null,
            ids: $data['ids'],
            metadatas: $data['metadatas'] ?? null,
            documents: $data['documents'] ?? null,
            images: $data['images'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'embeddings' => $this->embeddings,
            'ids' => $this->ids,
            'metadatas' => $this->metadatas,
            'documents' => $this->documents,
        ]);
    }
}
