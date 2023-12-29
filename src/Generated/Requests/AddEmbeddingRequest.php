<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Requests;

/**
 * Request model for adding items to collection.
 */
class AddEmbeddingRequest
{
    public function __construct(
        /**
         * Optional embeddings of the items to add.
         *
         * @var float[][]
         */
        public readonly ?array $embeddings,

        /**
         * Optional metadatas of the items to add.
         *
         * @var array<array<string, string>>
         */
        public readonly ?array $metadatas,

        /**
         * IDs of the items to add.
         *
         * @var string[]
         */
        public readonly array $ids,

        /**
         * Optional documents of the items to add.
         *
         * @var string[]
         */
        public readonly ?array $documents,

        public readonly ?array $images,

    )
    {
    }

    public static function create(array $data): self
    {
        return new self(
            embeddings: $data['embeddings'] ?? null,
            metadatas: $data['metadatas'] ?? null,
            ids: $data['ids'],
            documents: $data['documents'] ?? null,
            images: $data['images'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'embeddings' => $this->embeddings,
            'metadatas' => $this->metadatas,
            'ids' => $this->ids,
            'documents' => $this->documents,
        ];
    }
}